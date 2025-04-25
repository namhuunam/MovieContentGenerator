<?php

namespace NamHuuNam\MovieContentGenerator\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GeminiService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $modelId;

    /**
     * GeminiService constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('MovieContentGenerator.api_key');
        $this->modelId = config('MovieContentGenerator.model_id');
    }

    /**
     * Generate content using Gemini API
     *
     * @param string $name Movie title
     * @param string $content Movie description (can be empty)
     * @return string|null Generated content or null if error
     */
    public function generateContent($name, $content)
    {
        try {
            $promptTemplate = config('MovieContentGenerator.prompt_template');
            
            // Nếu content rỗng, sử dụng prompt template tùy chỉnh chỉ với name
            if (empty($content)) {
                $nameOnlyPromptTemplate = config('MovieContentGenerator.name_only_prompt_template', 
                    'Dựa trên tiêu đề phim "{name}", hãy viết một bài viết về phim chuẩn SEO với độ dài khoảng 150 đến 300 từ tránh trùng lặp nội dung với nội dung các website khác. Ngôn ngữ 100% tiếng việt, tuyệt đối không dùng Markdown, không chèn ảnh, không chèn bất kỳ link, và ký tự đặc biệt nào.');
                
                $prompt = str_replace('{name}', $name, $nameOnlyPromptTemplate);
                LoggerService::info("Sử dụng prompt chỉ có name cho phim: $name");
            } else {
                // Sử dụng prompt template mặc định với cả name và content
                $prompt = str_replace(['{name}', '{content}'], [$name, $content], $promptTemplate);
            }

            $response = $this->client->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->modelId}:generateContent?key={$this->apiKey}",
                [
                    'json' => [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    [
                                        'text' => $prompt
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => (float) config('MovieContentGenerator.temperature', 1),
                            'topP' => (float) config('MovieContentGenerator.top_p', 0.95),
                            'topK' => (int) config('MovieContentGenerator.top_k', 40),
                            'maxOutputTokens' => (int) config('MovieContentGenerator.max_output_tokens', 8192),
                            'responseMimeType' => 'text/plain',
                        ],
                    ],
                    'timeout' => 60 // Tăng timeout để đủ thời gian cho phản hồi lớn
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }

            LoggerService::error('Không nhận được dữ liệu phản hồi hợp lệ từ API Gemini: ' . json_encode($result));
            return null;
        } catch (GuzzleException $e) {
            LoggerService::error('Lỗi khi gọi API Gemini: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            LoggerService::error('Lỗi không xác định khi xử lý nội dung: ' . $e->getMessage());
            return null;
        }
    }
}
