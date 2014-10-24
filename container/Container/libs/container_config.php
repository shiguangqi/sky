<?php

require_once __DIR__ . '/Core/Container.php';
/*
 * container的配置文件
 */
if (get_cfg_var('env.name') == 'dev')
{
    Swoole::$php->config->setPath(__DIR__.'/../configs/dev/');
}
else
{
    Swoole::$php->config->setPath(__DIR__.'/../configs/');
}
Swoole\Loader::setRootNS('Container', __DIR__.'/Core');