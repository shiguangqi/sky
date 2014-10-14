<?php
define('DEBUG', 'on');
//必须设置此目录,PHP程序的根目录
define('WEBPATH', __DIR__);
//包含框架入口文件
require __DIR__ . '/libs/lib_config.php';
//开发环境的配置，如果此目录有配置文件，会优先选择
if (get_cfg_var('env.name') == 'dev')
{
    Swoole::$php->config->setPath(WEBPATH . '/configs/dev/');
}
Swoole::$php->runMVC();
