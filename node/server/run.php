<?php

if (get_cfg_var('env.name') == 'dev')
{
    $config = parse_ini_file(__DIR__.'/configs/dev/node.ini',true);
}
else
{
    $config = parse_ini_file(__DIR__.'/configs/node.ini',true);
}
defined("ROOT") || define("ROOT",__DIR__);
require __DIR__.'/Node.php';
/**
 * 产生类库的全局变量
 */
global $node;
$node = \Sky\Node::getInstance();
require __DIR__.'/Loger.php';
$node->setLoger(\Sky\Loger::getLoger($config['log']));
$node->init($config);
$node->run();