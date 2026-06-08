<?php

return [

    'api_key' => env('GEMINI_API_KEY'),

    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    'timeout' => (int) env('GEMINI_TIMEOUT', 30),

    'retry_times' => (int) env('GEMINI_RETRY_TIMES', 3),

];
