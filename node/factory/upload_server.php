<?php

if (get_cfg_var('env.name') == 'dev')
{
    $config = parse_ini_file(__DIR__.'/../configs/dev/node.ini',true);
}
else
{
    $config = parse_ini_file(__DIR__.'/../configs/node.ini',true);
}

require __DIR__."/../daemon/UploadServer.php";
$config['daemon']['upload_server']['pid'] = __DIR__."/..".$config['daemon']['upload_server']['pid'];
$up = new \Sky\UploadServer($config['daemon']['upload_server']);
require __DIR__.'/../Loger.php';
$fconfig = array(
    'type' => 'file',
    'file' => 'upload_server.log',
);
$up->setLoger(\Sky\Loger::getLoger($fconfig));
$up->start();