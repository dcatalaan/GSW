<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Chatbot RAG (Retrieval-Augmented Generation) con Groq API
 *
 * Flujo RAG completo:
 * 1. El usuario escribe CUALQUIER pregunta
 * 2. Se recuperan TODOS los productos relevantes de la BD (Retrieval)
 * 3. Se inyectan como contexto al LLM junto con las instrucciones
 * 4. El LLM genera una respuesta natural + lista de slugs recomendados
 * 5. Se buscan esos productos específicos para el panel "Productos encontrados"
 */
class ChatbotRAGService
{
    private string $groqApiKey;
    private string $groqModel;
    private string $groqUrl;

    public function __construct()
    {
        $this->groqApiKey = config('services.groq.key', '');
        $this->groqModel = config('services.groq.model', 'qwen/qwen3.8-27b');
        $this->groqUrl = config('services.groq.url', 'https://api.groq.com/openai/v1/chat/completions');
    }

    /**
     * RAG: Pregunta → Retrieval → Generation → Respuesta
     */
    public function chat(string $message, ?int $userId = null): array
    {
        $message = trim($message);

        if (empty($message)) {
            return [
                'response' => 'Hola, soy el asistente de GSWStore. Puedo ayudarte con productos, precios, recomendaciones o cualquier duda. ¿En qué te ayudo?',
                'products' => [],
                'intent' => 'greeting',
                'source' => 'empty',
            ];
        }

        // 1. RETRIEVAL — Buscar productos relevantes de la BD
        $contextProducts = $this->retrieveContextProducts($message);

        // 2. GENERATION — Enviar al LLM con contexto RAG
        $result = $this->callGroqLLM($message, $contextProducts);

        // 3. POST-GENERATION — Buscar los productos específicos que el LLM mencionó
        $result['products'] = $this->resolveMentionedProducts($result['response']);

        return $result;
    }

    /**
     * RETRIEVAL: Buscar productos para inyectar como contexto al LLM
     * (Estos NO se muestran en el panel — solo son contexto para que el LLM piense)
     */
    private function retrieveContextProducts(string $query): array
    {
        $lower = mb_strtolower($query);

        // Detectar categoría
        $categoryId = $this->detectCategoryId($lower);

        // 1. Si detecta categoría, traer todos de esa categoría
        if ($categoryId) {
            $results = Product::active()
                ->where('category_id', $categoryId)
                ->with('category')
                ->orderByDesc('is_featured')
                ->limit(10)
                ->get()
                ->toArray();

            if (!empty($results)) return $results;
        }

        // 2. Búsqueda textual — traer productos que coincidan
        $words = array_filter(explode(' ', $lower), fn($w) => mb_strlen($w) > 2);

        if (!empty($words)) {
            $results = Product::active()
                ->with('category')
                ->where(function ($q) use ($words) {
                    foreach ($words as $word) {
                        $q->orWhere('name', 'LIKE', "%{$word}%")
                          ->orWhere('description', 'LIKE', "%{$word}%")
                          ->orWhere('short_description', 'LIKE', "%{$word}%");
                    }
                })
                ->limit(10)
                ->get()
                ->toArray();

            if (!empty($results)) return $results;
        }

        // 3. Para todo lo demás (off-topic, general), traer todos los productos
        //    para que el LLM tenga el catálogo completo y elija cuáles recomendar
        return Product::active()
            ->with('category')
            ->orderByDesc('is_featured')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Detectar categoría por palabras clave
     */
    private function detectCategoryId(string $lower): ?int
    {
        // Normalizar: quitar acentos y tildes
        $normalized = preg_replace('/[áà]/u', 'a', $lower);
        $normalized = preg_replace('/[éè]/u', 'e', $normalized);
        $normalized = preg_replace('/[íì]/u', 'i', $normalized);
        $normalized = preg_replace('/[óò]/u', 'o', $normalized);
        $normalized = preg_replace('/[úù]/u', 'u', $normalized);

        $map = [
            1 => ['deporte', 'deportes', 'correr', 'running', 'ejercicio', 'gym', 'fitness', 'zapatillas', 'chaqueta deportiva', 'entrenar', 'maraton', 'cardio'],
            2 => ['tecnologia', 'electronica', 'audifono', 'audifonos', 'smartwatch', 'reloj', 'bluetooth', 'gps', 'computadora', 'laptop', 'auricular', 'tracker'],
            3 => ['hogar', 'casa', 'cocina', 'olla', 'sarten', 'antiadherente', 'decoracion', 'mueble'],
            4 => ['moda', 'ropa', 'mochila', 'accesorio', 'urban', 'fashion', 'bolsa'],
            5 => ['outdoor', 'camping', 'acampar', 'carpa', 'bicicleta', 'montana', 'senderismo', 'ciclismo', 'bici'],
        ];

        foreach ($map as $catId => $keywords) {
            foreach ($keywords as $kw) {
                if (mb_strpos($normalized, $kw) !== false) {
                    return $catId;
                }
            }
        }

        return null;
    }

    /**
     * GENERATION: Llamar al LLM de Groq con contexto RAG
     */
    private function callGroqLLM(string $message, array $contextProducts): array
    {
        $productContext = $this->buildProductContext($contextProducts);
        $systemPrompt = $this->buildSystemPrompt($productContext);

        if (empty($this->groqApiKey)) {
            return [
                'response' => 'La función de IA no está configurada. Agrega tu GROQ_API_KEY en el archivo .env para activar el chatbot.',
                'intent' => 'error',
                'source' => 'no_api_key',
            ];
        }

        // Intentar con el modelo principal (presupuesto total < 30s límite PHP)
        $result = $this->tryGroqModel($this->groqModel, $systemPrompt, $message, 12);
        if ($result) return $result;

        // Fallback a modelo secundario
        $result = $this->tryGroqModel('openai/gpt-oss-20b', $systemPrompt, $message, 10);
        if ($result) return $result;

        return [
            'response' => 'Disculpa, hay un problema de conexión. Intenta de nuevo en un momento.',
            'intent' => 'error',
            'source' => 'all_failed',
        ];
    }

    /**
     * Intentar generar respuesta con un modelo específico
     */
    private function tryGroqModel(string $model, string $systemPrompt, string $userMessage, int $timeout = 12): ?array
    {
        try {
            $apiResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->groqApiKey,
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()
            ->connectTimeout(5)
            ->timeout($timeout)
            ->post($this->groqUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.7,
                'max_tokens' => 600,
                'top_p' => 0.9,
            ]);

            if ($apiResponse->successful()) {
                $data = $apiResponse->json();
                $llmResponse = $data['choices'][0]['message']['content'] ?? null;

                if ($llmResponse) {
                    Log::info('Groq LLM success', ['model' => $model]);
                    return [
                        'response' => $this->stripEmojis($llmResponse),
                        'intent' => 'rag',
                        'source' => 'groq_llm',
                    ];
                }
            }

            Log::warning('Groq model error', [
                'model' => $model,
                'status' => $apiResponse->status(),
                'body' => substr($apiResponse->body(), 0, 300),
            ]);
        } catch (\Exception $e) {
            Log::error('Groq model exception', ['model' => $model, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * POST-GENERATION: Extraer slugs de productos mencionados en la respuesta del LLM
     * y buscarlos en la BD para mostrar en el panel "Productos encontrados"
     */
    private function resolveMentionedProducts(string $llmResponse): array
    {
        // Buscar links en formato [Nombre](/productos/slug) en la respuesta del LLM
        $pattern = '/\/productos\/([a-z0-9-]+)/i';
        preg_match_all($pattern, $llmResponse, $matches);

        if (empty($matches[1])) {
            return [];
        }

        // Obtener slugs únicos
        $slugs = array_unique($matches[1]);

        // Buscar productos en la BD por slugs
        $products = Product::active()
            ->with('category')
            ->whereIn('slug', $slugs)
            ->get()
            ->toArray();

        return $products;
    }

    /**
     * Eliminar emojis de la respuesta del LLM
     */
    private function stripEmojis(string $text): string
    {
        $text = preg_replace(
            '/[\x{1F300}-\x{1FAFF}\x{1F1E6}-\x{1F1FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}]/u',
            '',
            $text
        );
        // Colapsar espacios dobles que deje la limpieza
        return trim(preg_replace('/[ \t]{2,}/u', ' ', $text ?? ''));
    }

    /**
     * SYSTEM PROMPT — La clave del RAG
     * Instruye al LLM para responder CUALQUIER cosa pero siempre redirigir a la tienda
     */
    private function buildSystemPrompt(string $productContext): string
    {
        return <<<'PROMPT'
Eres "GSWStore Assistant", el asistente virtual inteligente de **GSWStore**, una tienda en línea de Costa Rica.

## TU PERSONALIDAD
- Eres amable y directo, y usas español costarricense natural
- Eres paciente y educado, incluso con preguntas off-topic
- Respondes como un vendedor experto que conoce todo el catálogo
- NUNCA usas emojis ni emoticones en tus respuestas; el tono amable viene de las palabras
- Eres conciso: máximo 4-5 oraciones por respuesta

## REGLAS FUNDAMENTALES (RAG)

### 1. SIEMPRE responde en base al catálogo de productos que te doy abajo — NUNCA inventes productos
### 2. SIEMPRE desvía la conversación hacia los productos de la tienda de forma natural
### 3. NUNCA digas "no tengo esa información" — en su lugar, redirige al catálogo
### 4. SIEMPRE menciona precios reales cuando hables de productos
### 5. SIEMPRE incluye al menos 1 link de producto en tu respuesta usando el formato: [Nombre](/productos/slug)
### 6. NUNCA incluyas emojis en tu respuesta bajo ninguna circunstancia
### 7. Si el usuario pregunta algo que NO es de la tienda, responde brevemente y redirige a un producto relacionado del catálogo
### 8. Si el usuario pregunta por una categoría (ej: "tecnología"), muestra TODOS los productos de esa categoría que estén en el catálogo abajo

## EJEMPLOS DE CÓMO MANEJAR PREGUNTAS:

**Usuario:** "¿Qué productos de tecnología tienen?"
**Tú:** "Tenemos opciones geniales. El [Smartwatch Fitness Tracker](/productos/smartwatch-fitness-tracker) con GPS por $129.99, y los [Audífonos Bluetooth Pro](/productos/audifonos-bluetooth-pro) con cancelación de ruido por $45.99. ¿Te interesa alguno?"

**Usuario:** "¿Qué tiempo hace hoy?"
**Tú:** "No tengo acceso al clima, pero si estás buscando algo para protegerte del sol o la lluvia, tenemos la [Chaqueta Impermeable Deportiva](/productos/chaqueta-impermeable-deportiva) que es impermeable y transpirable por $65.00. ¿Te interesa?"

**Usuario:** "¿Cuál es la capital de Francia?"
**Tú:** "Paris. Mientras tanto, si necesitas una mochila para tu próximo viaje, tenemos la [Mochila Urban Explorer](/productos/mochila-urban-explorer) resistente al agua con espacio para laptop por $39.99."

**Usuario:** "Cuéntame un chiste"
**Tú:** "Por aquí no somos comediantes, pero sí tenemos precios que te van a gustar. La [Mochila Urban Explorer](/productos/mochila-urban-explorer) está a solo $39.99. ¿Quieres que te cuente más?"

## FORMATO DE RESPUESTA — CRÍTICO
- Responde de forma natural y conversacional
- **IMPORTANTE**: Cuando menciones un producto, SIEMPRE usa este formato EXACTO con link:
  [Nombre del Producto](/productos/slug-del-producto)
- Ejemplo: Tengo la [Chaqueta Impermeable Deportiva](/productos/chaqueta-impermeable-deportiva) por $65.00
- Los slugs de los productos están en el catálogo abajo (campo "Link")
- **Solo menciona productos que EXISTAN en el catálogo de abajo**
- NUNCA uses emojis ni emoticones
- Al final, siempre pregunta si quiere más info
- Sé breve: máximo 4-5 oraciones

## CATÁLOGO DE PRODUCTOS DISPONIBLES:
{$productContext}
PROMPT;
    }

    /**
     * Construir contexto de productos para inyectar en el prompt
     */
    private function buildProductContext(array $products): string
    {
        if (empty($products)) {
            return "No hay productos disponibles temporalmente.";
        }

        $lines = [];
        foreach ($products as $product) {
            $cat = $product['category']['name'] ?? 'Sin categoría';
            $discount = '';
            if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']) {
                $pct = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100);
                $discount = " ({$pct}% descuento de \${$product['compare_price']})";
            }
            $featured = !empty($product['is_featured']) ? ' [DESTACADO]' : '';
            $stock = $product['stock'] ?? 0;
            $stockMsg = $stock > 10 ? 'Disponible' : ($stock > 0 ? "Solo quedan {$stock}" : 'Agotado');

            $lines[] = "- **{$product['name']}** | {$cat} | \${$product['price']}{$discount}{$featured}";
            $lines[] = "  Descripción: {$product['description']}";
            $lines[] = "  Estado: {$stockMsg} | SKU: {$product['sku']} | Link: /productos/{$product['slug']}";
            $lines[] = "";
        }

        return implode("\n", $lines);
    }
}
