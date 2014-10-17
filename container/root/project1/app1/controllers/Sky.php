<?php
namespace App1\Controller;
use Swoole;

class Sky extends Swoole\Controller
{
    public function home()
    {
        echo '<pre>';
//        $parmams['order'] = 'id desc';
//        $nodes = table('app_install')->gets($parmams);
//        print_r($nodes);
        print_r(Swoole::$php->config);
        $a = new \App1\Libs\Game();
        $a->h();
        $b = new \Project1\P();
        $b->test1();

    }
}