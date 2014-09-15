<?php
namespace Sky;


class Service
{
    public $sky;
    public $res;
    public $log;

    public $service;
    public $cmd;

    public function __construct($sky)
    {
        $this->sky = $sky;

        $this->res = $this->sky->res;
        $this->loger = $this->sky->log;
    }

    function setRes($service,$cmd)
    {
        $this->res->set($service,$cmd);
    }

    function send($fd,$data)
    {
        return $this->res->send($fd, $data);
    }

    public function error($fd,$code,$data='')
    {
        return $this->res->error($fd,$code,$data);
    }

    public function log($msg, $level = 1)
    {
        $this->loger->log($msg, $level);
    }
}