<?php
/**
 * 容器入口
 */

/**
 * swoole 初始化
 * copy 一份为container,重新定制 app使用和 server
 */
define('DEBUG', 'on');
define('WEBPATH', __DIR__);
define("CROOT",__DIR__.'/..');
require __DIR__ . '/libs/swoole_config.php';
$php = Swoole::getInstance();
/**
 * container 初始化
 * container使用swoole自动载入
 */
require __DIR__ . '/libs/container_config.php';
$c = Container::getInstance($php);
$c->run();