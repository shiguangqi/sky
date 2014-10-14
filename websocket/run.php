<?php
define('DEBUG', 'on');
define('WEBPATH', __DIR__);
define('WEBROOT', __DIR__);

if (get_cfg_var('env.name') == 'dev')
{
    define('SKY_HOST', '127.0.0.1');
    define('SKY_PORT', 9998);
}
else
{
    define('SKY_HOST', '119.147.176.30');
    define('SKY_PORT', 9998);
}


require __DIR__.'/../vendor/autoload.php';
Swoole\Loader::vendor_init();

$web = new App\WebsocketSvr();
$web->setLogger(new Swoole\Log\EchoLog(array('display'=>1)));

$config['server'] = array(
    'host' => '0.0.0.0',
    'port' => '9991',
);

if (get_cfg_var('env.name') == 'dev')
{
    $config['swoole'] = array(
        'worker_num' => 1,
        'max_request' => 0,
    );
} else {
    $config['swoole'] = array(
        'worker_num' => 1,
        'max_request' => 0,
    );
}
$opt = getopt('m::');

if (isset($opt['m']) and $opt['m'] == 'daemon')
{
    $config['swoole']['daemonize'] = true;
}

$server = new Swoole\Network\Server($config['server']['host'], $config['server']['port']);
$server->setProtocol($web);
$server->run($config['swoole']);