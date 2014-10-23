<?php
namespace App1;
use Swoole;

class Sky extends \Container\App
{
    public function __construct(\Swoole $swoole)
    {
        parent::__construct($swoole);
        $this->config = $this->pconfig;
    }

    public function home()
    {
        echo '<pre>';
        $parmams['order'] = 'id desc';
        $nodes = table('node')->gets($parmams);
        print_r($nodes);
    }
    public function j()
    {
        echo '11212';
        $this->swoole->http->redirect('http://www.baidu.com');
    }

}