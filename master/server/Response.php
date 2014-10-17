<?php
namespace Sky;

/*
*异步响应node节点，需要返回参数需要包含服务类型和命令类型
*/
class Response
{
    public $sky;
    public $service;
    public $cmd;

    public $code = array(
        0 => "系统错误",
        9000 => "参数错误",
        9001 => "服务不存在",
        9002 => "命令不存在",
        9003 => "参数错误",
        9005 => "节点不存在",
    );

    public $eof = "\r\n";

    public function __construct($sky)
    {
        $this->sky = $sky;
    }

    public function set($service,$cmd)
    {
        $this->service = $service;
        $this->cmd = $cmd;
    }

    public function serService($service)
    {
        $this->service = $service;
    }
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;
    }

    function send($fd,$data)
    {
        return $this->sky->server->send($fd, $this->pack($data));
    }
    function error($fd,$code,$data='')
    {
        if (array_key_exists($code,$this->code))
        {
            $msg = $this->code[$code]."\n";
        }
        else
        {
            $msg = $this->code[0];
        }
        if ($data)
        {
            $msg .= json_encode($data);
        }

        $this->send($fd,$msg);
    }

    function pack($data)
    {
        $package = array(
            'service' => $this->service,
            'cmd' => $this->cmd,
            'data' => $data,
        );
        return json_encode($package).$this->eof;
    }
}
