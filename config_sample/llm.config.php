<?php

return [
    'llm' => [
        'enabled' => false,
        'endpoint' => 'http://127.0.0.1:11434/api/generate',
        'model' => 'llama3',
        'timeout_seconds' => 15,
        'max_input_bytes' => 20000,
        'allow_remote_endpoint' => false,
    ],
];
