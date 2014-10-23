<?php
namespace App2;
use Swoole;

class Sky extends \Container\App
{
    public function home()
    {
        echo '<pre>';
        $parmams['order'] = 'id desc';
        $nodes = table('node')->gets($parmams);
        print_r($nodes);
        var_dump($this->config['app1']);
        var_dump($this->config['app2']);
        var_dump($this->pconfig['db']);
        $a = new \App2\Game();
        $a->h();
        $b = new \Project1\P();
        $b->test1();
    }
}