<?php

namespace App\Http\Controllers;

use Gemini\Enums\Role;
use Gemini\Data\Content;
use Illuminate\Http\Request;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Cache;

class GeminiController extends Controller
{
    function chat(Request $request) {
        $request->validate([
            'message' => 'required|string'
        ]);

        // Identify user (can be enhanced with auth later)
        $userId = $request->user()->id;
        $cacheKey = "chat_history_{$userId}";

        // Load chat history
        $history = Cache::get($cacheKey, []);

        if (empty($history)) {
            return response()->json([
                'reply' => 'No chat history found. Please start a new conversation.',
            ], 400);
        }

        // Create a chat instance with past context
        $chat = Gemini::chat(model: 'gemini-2.0-flash')->startChat(history: $history);

        // Send new message
        $userMessage = $request->input('message');
        $response = $chat->sendMessage($userMessage);

        // Clean formatting
        $cleanedResponse = preg_replace([
            '/```(?:\w+)?\n([\s\S]*?)\n```/',   // Code blocks
            '/`([^`]+)`/',                      // Inline code
            '/\*\*(.*?)\*\*/',                 // Bold
            '/\*(.*?)\*/',                     // Italic
            '/_(.*?)_/',                       // Underscore italic
            '/^\s*[-*+]\s+/m',                 // Bullet points
            '/^\s*#+\s*/m',                    // Headings
            '/^\s*\d+\.\s+/m'                  // Numbered lists
        ], [
            '$1', '$1', '$1', '$1', '$1', '', '', ''
        ], $response->text());

        // Remove all newlines and excess whitespace
        $cleanedResponse = preg_replace('/\s+/', ' ', $cleanedResponse); // Collapse all whitespace to single spaces
        $cleanedResponse = trim($cleanedResponse); // Remove leading/trailing space

        // Append new user and model messages to history
        $history[] = Content::parse(part: $userMessage, role: Role::USER);
        $history[] = Content::parse(part: $cleanedResponse, role: Role::MODEL); 

        // Save updated history (30 mins cache)
        Cache::put($cacheKey, $history, now()->addMinutes(30));

        return response()->json([
            'reply' => $cleanedResponse,
        ]);
    }

    function start(Request $request) {
        // Identify user (can be enhanced with auth later)
        $userId = $request->user()->id;
        $cacheKey = "chat_history_{$userId}";

        // Clear chat history
        Cache::forget($cacheKey);

        $mainRule = "MAIN RULE: Respond only in plain text, with no Markdown, no code blocks, and no special characters or formatting. I will parse your response directly in a frontend input box. This means:
- No newlines unless required
- No **bold**, _italics_, or ```
- No list bullets, numbers, or headings

Your response must look like it was typed as a normal SMS message.
Your response MUST be in indonesian language.
";

        $prePromt = "Anda adalah bot asisten kesehatan yang dirancang untuk mendukung pasien dengan menjawab pertanyaan mereka. Tugas Anda adalah memberikan panduan, saran sederhana, dan menyarankan pengobatan untuk membantu mengatasi keluhan mereka. Anda tidak dimaksudkan untuk menggantikan peran dokter, tetapi bertindak sebagai pendamping. Selalu hindari memberikan saran yang tegas atau pasti, dan ingatkan pasien untuk berkonsultasi dengan profesional kesehatan. Harap bantu pasien dengan pertanyaan berikut. (DO NOT FORMAT YOUR RESPONSE, REPLY IN PLAIN TEXT ONLY) Harap beri salam singkat setelah prompt ini.";


        // Create a chat instance with past context
        $chat = Gemini::chat(model: 'gemini-2.0-flash')->startChat();

        $response = $chat->sendMessage($mainRule . $prePromt);

        // Clean formatting
        $cleanedResponse = preg_replace([
            '/```(?:\w+)?\n([\s\S]*?)\n```/',   // Code blocks
            '/`([^`]+)`/',                      // Inline code
            '/\*\*(.*?)\*\*/',                 // Bold
            '/\*(.*?)\*/',                     // Italic
            '/_(.*?)_/',                       // Underscore italic
            '/^\s*[-*+]\s+/m',                 // Bullet points
            '/^\s*#+\s*/m',                    // Headings
            '/^\s*\d+\.\s+/m'                  // Numbered lists
        ], [
            '$1', '$1', '$1', '$1', '$1', '', '', ''
        ], $response->text());

        // Remove all newlines and excess whitespace
        $cleanedResponse = preg_replace('/\s+/', ' ', $cleanedResponse); // Collapse all whitespace to single spaces
        $cleanedResponse = trim($cleanedResponse); // Remove leading/trailing space

        $history[] = Content::parse(part: $prePromt, role: Role::USER);
        $history[] = Content::parse(part: $cleanedResponse, role: Role::MODEL);

        // Save updated history (30 mins cache)
        Cache::put($cacheKey, $history, now()->addMinutes(30));
        // Return the initial response
        return response()->json([
            'reply' => $cleanedResponse,
        ], 200);
        
    }
}
