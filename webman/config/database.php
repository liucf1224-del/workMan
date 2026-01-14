<?php
return  [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => '127.0.0.1',
            'port'        => '3306',
            'database'    => 'your_database',
            'username'    => 'your_username',
            'password'    => 'your_password',
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
            ],
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
        'data_warehouse' => [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST_WAREHOUSE'),
            'port'        => env('DB_PORT_WAREHOUSE'),
            'database'    => env('DB_NAME_WAREHOUSE'),
            'username'    => env('DB_USER_WAREHOUSE'),
            'password'    => env('DB_PASSWORD_WAREHOUSE'),
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      =>  env('DB_PREFIX_WAREHOUSE', ''),
            'strict'      => false,
            'engine'      => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
            ],
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
        'fast_admin' => [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST_FAST'),
            'port'        => env('DB_PORT_FAST'),
            'database'    => env('DB_NAME_FAST'),
            'username'    => env('DB_USER_FAST'),
            'password'    => env('DB_PASSWORD_FAST'),
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      =>  env('DB_PREFIX_FAST', ''),
            'strict'      => false,
            'engine'      => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
            ],
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ]


    ],
];