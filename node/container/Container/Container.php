<?php
/**
 * Created by PhpStorm.
 * User: shiguangqi
 * Date: 14-10-14
 * Time: 下午2:49
 */

class Container
{
    public $config;
    static public $container;
    public $node;

    public $log;

    public $status;

    public $ctl; //容器控制

    public function __construct($config,$node=null)
    {
        $this->config = $config;
        $this->ctl = new \Container\Ctl($this);
        if (!empty($node))
        {
            $this->node = $node;
        }
    }

    static function getInstance($config)
    {
        if (!self::$container)
        {
            self::$container = new self($config);
        }
        return self::$container;
    }

    public function run()
    {
        if (!empty($this->config))
        {
            $protocol = $this->config['protocol'];
            if (!empty($protocol))
            {
                $workers = array();
                foreach ($protocol as $name => $p)
                {
                    //协议是开启状态
                    if ($p == 1)
                    {
                        switch ($name)
                        {
                            case 'http':
                                $pid_file = $this->config[$name]['pid'];
                                if (is_file($pid_file))
                                {
                                    $pid = file_get_contents($pid_file);
                                    $this->log("http server 已经在执行 pid=".$pid);
                                }
                                else
                                {
                                    $worker = new \swoole_process(array($this, 'startHttpServer'), false, true);
                                    $pid = $worker->start();
                                    $workers[$pid] = $worker;
                                    $this->log("start service http server ok");
                                }
                                $this->status[$name] = file_get_contents($pid_file);
                                break;
                        }
                    }
                    while(count($workers) > 0)
                    {
                        $ret = \swoole_process::wait();
                        unset($workers[$ret['pid']]);
                    }
                }
            }
            else
            {
                exit("config wrong");
            }
        }
    }

    //不支持传惨,暂时写在这里
    public function startHttpServer(\swoole_process $worker)
    {
        if (isset($this->node->client))
        {
            $this->node->client->close();
        }
        $worker->exec("/bin/sh",array($this->config['http']['init'],"_start"));
    }

    public function stopServer($name)
    {
        $this->status[$name] = 0; //标记server状态为0
        $init = $this->config[$name]['init'];
        exec($init." _stop",$output,$return);
    }

    public function startWsServer()
    {

    }

    public function startWupServer()
    {

    }

    public function setLoger($loger)
    {
        $this->log = $loger;
    }

    public function log($msg)
    {
        $this->log->put($msg);
    }

}