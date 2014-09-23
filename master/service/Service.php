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

    /*
     * params $ip
     *
     * return fd
     */
    public function getNodeByIp($ip)
    {
        $ip = strval($ip);
        if ($ip && !empty($this->sky->nodes))
        {
            foreach ($this->sky->nodes as $fd => $info)
            {
                if ($info['host'] == $ip)
                {
                    return $fd;
                }
            }
        }
        return false;
    }

    public function getCtlByIp($ip)
    {
        $ip = strval($ip);
        if ($ip && !empty($this->sky->ctl))
        {
            foreach ($this->sky->ctl as $fd => $info)
            {
                if ($info['host'] == $ip)
                {
                    return $fd;
                }
            }
        }
        return false;
    }
}