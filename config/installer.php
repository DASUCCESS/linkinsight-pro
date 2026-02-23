<?php

return [
    'required_php_version' => '8.1',

    'required_extensions' => [
        'bcmath',
        'ctype',
        'fileinfo',
        'json',
        'mbstring',
        'openssl',
        'pdo',
        'tokenizer',
        'xml',
        'curl',
    ],

    'writable_paths' => [
        'storage',
        'bootstrap/cache',
    ],
];
