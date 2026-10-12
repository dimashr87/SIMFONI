<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SummarizationService
{
    protected $client;
    protected $geminiClient;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
        ]);

        $this->geminiClient = new Client([
            'base_uri' => "https://generativelanguage.googleapis.com/v1beta/",
        ]);
    }

    public function summarize($text)
    {
        try {
            // === Coba dengan OpenAI (ChatGPT) ===
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo', // Bisa gpt-4 juga
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah asisten yang membantu merangkum teks.'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Silakan rangkum beberapa teks berikut dan buatkan rekomendasinya dalam bahasa Indonesia dan satu kalimat:\n\n" . $text
                        ],
                    ],
                    'max_tokens' => 250,
                    'temperature' => 0.5,
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['choices'][0]['message']['content'] ?? 'Ringkasan tidak tersedia.';
        } catch (\Exception $e) {
            // === Fallback ke Gemini kalau OpenAI gagal ===
            try {
                $response = $this->geminiClient->post('models/gemini-2.5-flash:generateContent', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        "x-goog-api-key" => env('GEMINI_API_KEY')
                    ],
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => "Ringkas teks berikut dalam bahasa Indonesia dan buat rekomendasi satu kalimat:\n\n" . $text]
                                ]
                            ]
                        ]
                    ],
                ]);

                $body = json_decode($response->getBody(), true);
                return $body['candidates'][0]['content']['parts'][0]['text'] ?? 'Ringkasan tidak tersedia (Gemini).';
            } catch (\Exception $geminiError) {
                return 'Terjadi kesalahan di OpenAI & Gemini: ' . $geminiError->getMessage();
            }
        }
    }
}
