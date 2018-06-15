<?php
return [
    'entity' => [
        
    ],

    'route' => [

        'prefix' => 'admin',

        'namespace' => 'Eav\\Controllers',

        'middleware' => ['web', 'admin'],
    ],

    'database' => [
//        // Database connection for following tables.
//        'connection' => '',
    ],

    'version' => '0.1',

    'extensions' => [

    ],
];
