<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de búsqueda semántica usando pgvector y sentence-transformers
 *
 * Genera embeddings vectoriales para las descripciones de productos
 * y permite búsquedas por similitud coseno.
 */
class SemanticSearchService
{
    private string $embeddingUrl;
    private int $dimensions;

    public function __construct()
    {
        $this->embeddingUrl = config('semantic.embedding_url', 'http://localhost:8000/embed');
        $this->dimensions = config('semantic.dimensions', 384);
    }

    /**
     * Generar embedding para un texto dado
     */
    public function generateEmbedding(string $text): array
    {
        try {
            // Intentar generar embedding via API local de sentence-transformers
            $response = Http::timeout(30)
                ->post($this->embeddingUrl, [
                    'text' => $text,
                ]);

            if ($response->successful()) {
                return $response->json('embedding', []);
            }

            Log::warning('Embedding API response not successful', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->generateFallbackEmbedding($text);
        } catch (\Exception $e) {
            Log::error('Error generating embedding', ['error' => $e->getMessage()]);
            return $this->generateFallbackEmbedding($text);
        }
    }

    /**
     * Generar embedding de fallback (TF-IDF simplificado)
     * Se usa cuando el servicio de embeddings no está disponible
     */
    private function generateFallbackEmbedding(string $text): array
    {
        $words = str_word_count(strtolower($text), 1);
        $wordFreq = array_count_values($words);
        arsort($wordFreq);

        // Generar vector determinístico basado en las palabras
        $embedding = array_fill(0, $this->dimensions, 0.0);

        $i = 0;
        foreach ($wordFreq as $word => $freq) {
            if ($i >= $this->dimensions) break;
            // Usar hash de la palabra como índice
            $hash = crc32($word) % $this->dimensions;
            $embedding[$hash] += $freq / count($words);
            $i++;
        }

        // Normalizar el vector
        $norm = sqrt(array_sum(array_map(fn($v) => $v * $v, $embedding)));
        if ($norm > 0) {
            $embedding = array_map(fn($v) => $v / $norm, $embedding);
        }

        return $embedding;
    }

    /**
     * Generar embedding para un producto y guardarlo
     */
    public function generateProductEmbedding(Product $product): ProductEmbedding
    {
        // Construir texto representativo del producto
        $text = sprintf(
            "%s. %s. %s. Categoría: %s. Precio: $%s",
            $product->name,
            $product->short_description ?? '',
            $product->description,
            $product->category->name ?? 'Sin categoría',
            number_format($product->price, 2)
        );

        $embedding = $this->generateEmbedding($text);

        // Eliminar embedding anterior si existe
        $product->embeddings()->delete();

        return $product->embeddings()->create([
            'embedding' => $embedding,
            'model_name' => config('semantic.model', 'all-MiniLM-L6-v2'),
            'created_at' => now(),
        ]);
    }

    /**
     * Regenerar embeddings para todos los productos
     */
    public function regenerateAllEmbeddings(): int
    {
        $products = Product::active()->with('category')->get();
        $count = 0;

        foreach ($products as $product) {
            $this->generateProductEmbedding($product);
            $count++;
        }

        return $count;
    }

    /**
     * Búsqueda semántica: encontrar productos similares a una consulta
     * En SQLite sin servicio de embeddings, retorna vacío (se usa búsqueda textual)
     */
    public function semanticSearch(string $query, int $limit = 10, float $minScore = 0.3): array
    {
        // En SQLite sin pgvector, la búsqueda semántica no es viable
        // El fallback TF-IDF genera embeddings de baja calidad y consume mucho tiempo
        if (config('database.default') !== 'pgsql') {
            Log::info('Semantic search skipped on SQLite — using text search only');
            return [];
        }

        // Generar embedding de la consulta (solo en PostgreSQL con pgvector)
        $queryEmbedding = $this->generateEmbedding($query);

        if (empty($queryEmbedding)) {
            return [];
        }

        $queryVector = '[' . implode(',', $queryEmbedding) . ']';

        // Búsqueda con pgvector
        $results = Product::selectRaw('products.*, 
                1 - (pe.embedding <=> ?::vector) AS similarity_score',
                [$queryVector])
            ->join('product_embeddings as pe', 'pe.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->having('similarity_score', '>=', $minScore)
            ->orderByDesc('similarity_score')
            ->limit($limit)
            ->with('category')
            ->get()
            ->toArray();

        return $results;
    }

    /**
     * Búsqueda híbrida: combina semántica + textual
     */
    public function hybridSearch(string $query, int $limit = 10): array
    {
        // Búsqueda semántica
        $semanticResults = $this->semanticSearch($query, $limit);

        // Búsqueda textual tradicional
        $textResults = Product::active()
            ->search($query)
            ->with('category')
            ->limit($limit)
            ->get()
            ->toArray();

        // Combinar resultados con peso: semántica 70%, textual 30%
        $combined = [];

        foreach ($semanticResults as $item) {
            $id = $item['id'];
            $combined[$id] = $item;
            $combined[$id]['_semantic_score'] = $item['similarity_score'] ?? 0;
            $combined[$id]['_text_score'] = 0;
        }

        foreach ($textResults as $item) {
            $id = $item['id'];
            if (isset($combined[$id])) {
                $combined[$id]['_text_score'] = 1.0;
            } else {
                $combined[$id] = $item;
                $combined[$id]['_semantic_score'] = 0;
                $combined[$id]['_text_score'] = 1.0;
            }
        }

        // Calcular score combinado
        foreach ($combined as &$item) {
            $item['combined_score'] = ($item['_semantic_score'] * 0.7) + ($item['_text_score'] * 0.3);
        }
        unset($item);

        // Ordenar por score combinado
        usort($combined, fn($a, $b) => $b['combined_score'] <=> $a['combined_score']);

        return array_slice($combined, 0, $limit);
    }

    /**
     * Calcular similitud coseno entre dos vectores
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        $len = min(count($a), count($b));

        for ($i = 0; $i < $len; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) return 0;

        return $dotProduct / ($normA * $normB);
    }
}
