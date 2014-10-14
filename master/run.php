<?php

define('DEBUG', 'on');
define('WEBPATH', __DIR__);
defined("ROOT") || define("ROOT",__DIR__);
require __DIR__.'/Sky.php';
require __DIR__ . '/../vendor/autoload.php';
Swoole\Loader::vendor_init();
Swoole\Loader::setRootNS('Sky', __DIR__);

if (get_cfg_var('env.name') == 'dev')
{
    $config = parse_ini_file(__DIR__.'/configs/dev/master.ini',true);
    Swoole::$php->config->setPath(__DIR__.'/configs/dev/');
}
else
{
    $config = parse_ini_file(__DIR__.'/configs/master.ini',true);
    Swoole::$php->config->setPath(__DIR__.'/configs/');
}

global $sky;
$sky = Sky::getInstance();
$sky->init($config);
$sky->setWhiteList($config['white_list']);

//$sky->setLoger(new Swoole\Log\FileLog(__DIR__.'/log/master.log'));
$sky->setLoger(new Swoole\Log\EchoLog(array('display' => 1)));
//设置swoole日志
$setting = array(
    'log_file' => __DIR__.'/log/swoole.log',
    //'daemonize' => 1,
);
$sky->run();
