<?php
namespace Sky;

class Daemon
{
    public $config;
    public $daemons;
    public $node;

    private $cmd_header = "cmd ";
    private $protocol_end = "\r\n";

    public function __construct($config,$node)
    {
        if (!empty($config))
        {
            foreach ($config as $k => $c)
            {
                if (isset($c['pid']))
                {
                    $config[$k]['pid'] = $c['pid'];
                }
                if (isset($c['log_file']))
                {
                    $config[$k]['log_file'] = $c['log_file'];
                }
            }
            $this->config = $config;
        }
        $this->node = $node;
        $this->autostart();
    }

    //启动配置的服务
    public function autostart()
    {
        $workers = array();
        foreach ($this->config as $name => $cc)
        {
            if ($cc['auto_start'] == 1)
            {
                switch ($cc['name'])
                {
                    case 'upload_server':
                        if (is_file($cc['pid']))
                        {
                            $pid = file_get_contents($cc['pid']);
                            $this->node->log("upload server 已经在执行 pid=".$pid);
                        }
                        else
                        {
                            $worker = new \swoole_process(array($this, 'doStart'), false, true);
                            $worker->init = $this->config['upload_server']['init'];
                            $pid = $worker->start();
                            $workers[$pid] = $worker;
                            $this->node->log("start service upload server ok");
                        }
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

    //子进程
    function doStart(\swoole_process $worker)
    {
        //important
        if (isset($this->node->client))
        {
            $this->node->client->close();
        }
        $worker->exec("/bin/sh",array($worker->init,"_start"));
    }

    public function getDaemons()
    {
        foreach ($this->config as $name => $cc)
        {
            if (file_exists($cc['pid']))
            {
                $this->config[$name]['_pid'] = file_get_contents($cc['pid']);
            }
            else
            {
                $this->config[$name]['_pid'] = 0;
            }
        }
        return $this->config;
    }

    function cmd($data)
    {
        $params = $data['content'];
        $client = $data['client'];
        $name = $params['data']['s'];
        $cmd = $params['cmd'];
        if (!empty($name) && array_key_exists($name,$this->config))
        {
            switch ($cmd)
            {
                case 'start_service':
                    $this->startDaemon($params,$client);
                    break;
                case 'stop_service':
                    $this->startDaemon($params,$client);
                    break;
                case 'restart_service':
                    $this->restartDaemon($params,$client);
                    break;
            }
        }
    }

    public function startDaemon($params,$client)
    {
        $name = $params['data']['s'];
        $worker = new \swoole_process(array($this, 'doStart'), false, true);
        $worker->init = $this->config[$name]['init'];
        $worker->start();
        $ret = \swoole_process::wait();
        if ($ret['code'] === 0)
        {
            $output[] = "start {$name} success";
        }
        else
        {
            $output[] = "start {$name} failed";
        }
        $client->send($this->response($params,$output,'start_service'));
        return $ret['code'];
    }

    /*
     * 关闭不需要使用子进程
     */
    public function stopDaemon($params,$client)
    {
        $name = $params['data']['s'];
        $init = $this->config[$name]['init'];
        exec($init." _stop",$output,$return);
        if ($return === 0)
        {
            $output[] = "stop {$name} success";
            $params['status'] = 0;
        }
        else
        {
            $output[] = "stop {$name} failed";
            $params['status'] = 1;
        }
        $client->send($this->response($params,$output,'start_service'));
        return $return;
    }

    public function restartDaemon($params,$client)
    {
        if (!$this->stopDaemon($params,$client))
        {
            $this->startDaemon($params,$client);
        }
    }

    public function response($params,$output,$type)
    {
        $o = implode("\n",$output);
        $data = $params['content'];
        $line = '';
        switch ($type)
        {
            case 'stop_monitor' :
            case 'start_monitor' :
                $line = $this->cmd_header."_{$type} -s {$params['status']} -m {$data['data']['m']} -fd {$data['data']['fd']} -c {$data['data']['c']} -n {$data['data']['sn']} -o $o ".$this->protocol_end;
                break;
        }
        return $line;
    }
}