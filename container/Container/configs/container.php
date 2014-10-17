<?php
$container['project'] = array(
    'project1' => array(
        'host' => '0.0.0.0',
        'port' => 7000,
        'procotol' => 'http',
        'auto_start' => 1,
        'swoole' => array(
            'worker_num' => 1,
            'max_request' => 5000,
            'daemonize' => 1,
        ),
        'pid_path' => __DIR__.'/../run',
        'init_path' => __DIR__.'/../init.d',
        'log_path' => __DIR__.'/../log',
    ),

    'project2' => array(
        'host' => '0.0.0.0',
        'port' => 7001,
        'procotol' => 'http',
        'auto_start' => 0,
        'swoole' => array(
            'worker_num' => 1,
            'max_request' => 5000,
            'daemonize' => 1,
        ),
    ),
);

return $container;