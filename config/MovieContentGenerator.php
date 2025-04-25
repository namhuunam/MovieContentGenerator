<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the Gemini API
    |
    */

    'api_key' => env('GEMINI_API_KEY', ''),
    'model_id' => env('GEMINI_MODEL_ID', 'gemini-1.5-flash-8b'),
    'prompt_template' => env('GEMINI_PROMPT_TEMPLATE', 
        'Dựa trên tiêu đề {name} và mô tả {content}, hãy viết một bài viết về phim chuẩn SEO với độ dài khoảng 150 đến 300 từ tránh trùng lặp nội dung với nội dung các website khác. Ngôn ngữ 100% tiếng việt, tuyệt đối không dùng Markdown, không chèn ảnh, không chèn bất kỳ link, và ký tự đặc biệt nào.'
    ),
    
    'name_only_prompt_template' => env('GEMINI_NAME_ONLY_PROMPT_TEMPLATE', 
        'Dựa trên tiêu đề phim "{name}", hãy viết một bài viết về phim chuẩn SEO với độ dài khoảng 150 đến 300 từ tránh trùng lặp nội dung với nội dung các website khác. Ngôn ngữ 100% tiếng việt, tuyệt đối không dùng Markdown, không chèn ảnh, không chèn bất kỳ link, và ký tự đặc biệt nào.'
    ),

    // Gemini API generation parameters
    'temperature' => env('GEMINI_TEMPERATURE', 1),
    'top_p' => env('GEMINI_TOP_P', 0.95),
    'top_k' => env('GEMINI_TOP_K', 40),
    'max_output_tokens' => env('GEMINI_MAX_OUTPUT_TOKENS', 8192),

    'log_file' => storage_path('logs/moviecontentgenerator.log'),
];
