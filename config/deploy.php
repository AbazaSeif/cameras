<?php

return [
    'repository'=>[
        'url' => env('REPOSITORY_URL'),
        'owner' => env('REPOSITORY_OWNER'),
        'name' => env('REPOSITORY_NAME'),
    ],
    'project_path' => env('PROJECT_PATH'),
    'envoy' => env('ENVOY'),
    'php_service_name' => env('PHP_SERVICE_NAME', 'php7.0-fpm'),
];