<?php
define('DEBUG', 'on');
define('WEBPATH', __DIR__);
define('WEBROOT', 'http://local.sky.duowan.com');

require __DIR__.'/../vendor/autoload.php';
Swoole\Loader::vendor_init();

if (get_cfg_var('env.name') == 'dev')
{
    Swoole::$php->config->setPath(__DIR__.'/apps/configs/dev/');
}
Swoole::getInstance()->runMVC();