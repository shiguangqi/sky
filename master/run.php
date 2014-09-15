<?php

if (get_cfg_var('env.name') == 'dev')
{
    $config = parse_ini_file(__DIR__.'/configs/dev/master.ini',true);
}
else
{
    $config = parse_ini_file(__DIR__.'/configs/master.ini',true);
}
defined("DEBUG") || define("DEBUG",true);
defined("ROOT") || define("ROOT",__DIR__);

require __DIR__.'/Sky.php';
/**
 * 产生类库的全局变量
 */
global $sky;
$sky = Sky::getInstance();
$sky->init($config);
$sky->setWhiteList($config['white_list']);
require __DIR__.'/Loger.php';
$sky->setLoger(\Sky\Loger::getLoger($config['log']));
$sky->run();
