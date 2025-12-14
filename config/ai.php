<?php

return [
    'provider' => 'openai',

    'model' => env('AI_MODEL', 'gpt-4o-mini'),

    'timeout' => 5, // seconds

    'max_retries' => 1,
];
