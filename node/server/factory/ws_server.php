#! /usr/bin/php
<?php
define('DEBUG', 'on');
define('WEBPATH', __DIR__.'/../../container/apps');
require __DIR__ . '/../../../vendor/autoload.php';
Swoole\Loader::vendor_init();
if (get_cfg_var('env.name') == 'dev')
{
    $http_ini_file = __DIR__.'/../../configs/dev/http.ini';
}
else
{
    $http_ini_file = __DIR__.'/../../configs/http.ini';
}
Swoole\Config::$debug = false;
$http = new \Swoole\Protocol\HttpServer();
$http->loadSetting($http_ini_file);
$http->setDocumentRoot(WEBPATH);
$http->setLogger(new \Swoole\Log\FileLog('/tmp/http.log')); //Logger

$enable_ssl = false;
$server = new \Swoole\Network\Server('0.0.0.0', 8888, $enable_ssl);
$server->setProtocol($http);
//$server->daemonize(); //作为守护进程
$server->run(array('worker_num' => 1, 'max_request' => 5000, 'log_file' => '/tmp/swoole.log'));
