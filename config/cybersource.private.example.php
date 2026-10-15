<?php

return [
    'environment' => 'test',
    'currency' => 'GTQ',
    'operation' => 'sale',
    'site' => [
        'base_url' => 'https://industrialcollectors.com',
    ],
    'secure_acceptance' => [
        'profile_id' => 'REPLACE_WITH_PROFILE_ID',
        'access_key' => 'REPLACE_WITH_ACCESS_KEY',
        'secret_key' => 'REPLACE_WITH_SECURE_ACCEPTANCE_SECRET_KEY',
    ],
    'security' => [
        'admin_token' => 'REPLACE_WITH_LONG_RANDOM_INTERNAL_TOKEN',
    ],
    'mail' => [
        'enabled' => true,
        'from_email' => 'labrocaferreterias01@gmail.com',
        'from_name' => 'La Broca Ferreterías',
        'reply_to' => 'labrocaferreterias01@gmail.com',
        'envelope_sender' => 'labrocaferreterias01@gmail.com',
    ],
];
