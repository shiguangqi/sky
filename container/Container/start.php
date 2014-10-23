<?php
/**
 * 容器入口
 */

define('DEBUG', 'on');
define('WEBPATH', __DIR__);
define("CROOT",'/home/shiguangqi/workspace/sky/container');//__DIR__.'/..');
require __DIR__ . '/../../vendor/autoload.php';
Swoole\Loader::vendor_init();
/**
 * container 初始化
 * container使用swoole自动载入
 */
require __DIR__ . '/libs/container_config.php';
$c = Container::getInstance($php);
$c->run();