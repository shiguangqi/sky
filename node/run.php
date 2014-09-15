<?php

if (get_cfg_var('env.name') == 'dev')
{
    $config = parse_ini_file(__DIR__.'/configs/dev/node.ini',true);
}
else
{
    $config = parse_ini_file(__DIR__.'/configs/node.ini',true);
}

require __DIR__.'/Node.php';
/**
 * 产生类库的全局变量
 */
global $node;
$node = \Sky\Node::getInstance();
$node->run($config);