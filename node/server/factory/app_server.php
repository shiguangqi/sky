#! /usr/bin/php
<?php
define('DEBUG', 'on');
define('WEBPATH', __DIR__.'/../../container/apps/web');
require __DIR__ . '/../../../vendor/autoload.php';
Swoole\Loader::vendor_init();

if (get_cfg_var('env.name') == 'dev')
{
    //$config = parse_ini_file(__DIR__.'/../configs/dev/node.ini',true);
    $http_ini_file = __DIR__.'/../../configs/dev/http.ini';
}
else
{
    //$config = parse_ini_file(__DIR__.'/../configs/node.ini',true);
    $http_ini_file = __DIR__.'/../../configs/http.ini';
}

Swoole\Config::$debug = false;

$server = Swoole\Protocol\AppServer::create($http_ini_file);
$server->setAppPath(WEBPATH.'/apps/');                                 //设置应用所在的目录
$server->setDocumentRoot(WEBPATH);
$server->setLogger(new \Swoole\Log\EchoLog(true)); //Logger
//$server->daemonize();                                                  //作为守护进程
$server->run(array('worker_num' => 1, 'max_request' => 5000, 'log_file' => '/tmp/swoole.log'));
