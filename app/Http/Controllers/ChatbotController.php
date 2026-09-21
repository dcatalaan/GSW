<?php

namespace App\Http\Controllers;

use App\Services\ChatbotRAGService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    /**
     * Procesar mensaje del chatbot (API endpoint)
     */
    public function chat(Request $request, ChatbotRAGService $chatbot)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = $request->input('message');

        // Obtener respuesta RAG
        $result = $chatbot->chat($message);

        // Guardar en historial de sesión
        $history = Session::get('chat_history', []);
        $history[] = [
            'role' => 'user',
            'content' => $message,
            'time' => now()->format('H:i'),
        ];
        $history[] = [
            'role' => 'bot',
            'content' => $result['response'],
            'products' => $result['products'],
            'time' => now()->format('H:i'),
        ];
        Session::put('chat_history', array_slice($history, -30));

        return response()->json([
            'success' => true,
            'response' => $result['response'],
            'products' => $result['products'],
            'intent' => $result['intent'],
        ]);
    }

    /**
     * Obtener historial de conversación
     */
    public function history()
    {
        $history = Session::get('chat_history', []);
        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Limpiar historial de conversación
     */
    public function clear()
    {
        Session::forget('chat_history');
        return response()->json(['success' => true]);
    }
}
