<?php
$container['project'] = array(
    'project1' => array(
        'name' => 'project1',
        'host' => '0.0.0.0',
        'port' => 7000,
        'enable_ssl' => false,
        'protocol' => 'http',
        'auto_start' => 1,
        'swoole' => array(
            'worker_num' => 1,
            'max_request' => 5000,
            //'daemonize' => 1,
        ),
        'pid_path' => CROOT.'/run',
        'init_path' => CROOT.'/init.d',
        'log_path' => CROOT.'/log',

        'request' => array(
            'default_page' => 'index.php',
        ),
        'server' => array(
            'max_request' => 2000,
            'webroot' => 'http://127.0.0.1:7777',
            'document_root' => CROOT.'/root/project1',
            'process_rename' => 1,
            'keepalive' => 1,
            //'gzip_open' => 1,
            'user' => 'www-data',
            'expire_open' => 1,
        ),
        'session' =>  array(
            'cookie_life' => 1800,
            'session_life' => 1800,
            'cache_url' => "file://localhost#sess",
        ),
        'access' => array(
            'deny_dir' => "libs,class,templates",
            'static_dir' => 'static/,',
            'static_ext' => 'js,jpg,gif,png,css,html',
            'dynamic_ext' => 'dynamic_ext',
            'post_maxsize' => 'post_maxsize'
        ),

        'apps' => array(
            'charset' => 'utf-8',
            'do_static' => 'on',
            'apps_path' => '',
            'auto_reload' => 1,
        ),
    ),
    'project2' => array(
        'name' => 'project2',
        'host' => '0.0.0.0',
        'port' => 7001,
        'protocol' => 'http',
        'auto_start' => 0,
        'swoole' => array(
            'worker_num' => 1,
            'max_request' => 5000,
            'daemonize' => 1,
        ),
    ),
);

return $container;