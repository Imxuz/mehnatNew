<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function getAiResponse(Request $request){
        $apiUrl = 'http://172.17.110.163:8004/v1/completions';

        $prompt = "<|im_start|>system\n{$request->system}<|im_end|>\n" .
            "<|im_start|>user\n{$request->user}<|im_end|>\n" .
            "<|im_start|>assistant\n" .
            "<think>\n\n</think>\n\n";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->timeout(120)
                ->connectTimeout(10)
                ->post($apiUrl, [
                    'model' => 'Qwen/Qwen3-14B',
                    'prompt' => $prompt,
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                    'repetition_penalty' => 1.05,
                    'top_p' => 0.8,
                    'stream' => false,
                    'enable_thinking' => false,
                    'chat_template_kwargs' => [
                        'enable_thinking' => false
                    ],
                ]);

            if ($response->successful()) {
                return trim($response->json('choices.0.text'));
            }
            return null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("vLLM ulanishda xato: " . $e->getMessage());
            return "TIME_OUT_MESSAGE";
        } catch (\Exception $e) {
            Log::error("vLLM xatolik: " . $e->getMessage());
            return null;
        }
    }
}
