<?php
namespace App2\Controller;
use Swoole;

class Sky
{
    public function home()
    {
        echo '<pre>';
//        $parmams['order'] = 'id desc';
//        $nodes = table('node')->gets($parmams);
//        print_r($nodes);
        var_dump(Swoole::$php->config['app1']);
        var_dump(Swoole::$php->config['app2']);
        $a = new \App2\Libs\Game();
        $a->h();
        $b = new \Project1\P();
        $b->test1();
    }
}