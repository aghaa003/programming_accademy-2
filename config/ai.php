<?php

return [
    'ollama_host'         => env('OLLAMA_HOST', 'http://localhost:11434'),
    'ollama_model'        => env('OLLAMA_MODEL', 'qwen3-coder:480b-cloud'),
    'ollama_vision_model' => env('OLLAMA_VISION_MODEL', 'qwen3-vl:235b-cloud'),
];
