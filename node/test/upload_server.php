<?php
$config = parse_ini_file(__DIR__.'/../configs/dev/node.ini',true);
require __DIR__."/../daemon/UploadServer.php";

$config['daemon']['upload_server']['pid'] = __DIR__."/..".$config['daemon']['upload_server']['pid'];
$up = new \Sky\UploadServer($config['daemon']['upload_server']);
$up->start();