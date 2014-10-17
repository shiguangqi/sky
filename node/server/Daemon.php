<?php
namespace Sky;

class Daemon
{
    public $config;
    public $daemons;
    public $node;

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
                            $worker = new \swoole_process(array($this, 'startUploadServer'), false, true);
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

    function startUploadServer(\swoole_process $worker)
    {
        //important
        if (isset($this->node->client))
        {
            $this->node->client->close();
        }
        $worker->exec("/bin/sh",array($this->config['upload_server']['init'],"_start"));
    }
    public function stopall()
    {
        if (!empty($this->config))
        {
            foreach ($this->config as $name => $cc)
            {
                $this->stop($name);
            }
        }
    }

    public function stop($name)
    {
        $init = $this->config[$name]['init'];
        exec($init." _stop",$output,$return);
    }

    public function restart($name)
    {
        $this->stop($name);
        $this->start($name);
    }

    public function checkRunning($name)
    {
        if (isset($this->config[$name]))
        {
            return is_file($this->config[$name]['pid']);
        }
        else
        {
            return false;
        }
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
        $return = $data;
        if (!empty($return['data']['s']) && array_key_exists($return['data']['s'],$this->config))
        {
            switch ($return['cmd'])
            {
                case 'start_service':
                    $worker = new \swoole_process(array($this, 'startUploadServer'), false, true);
                    $worker->start();
                    $ret = \swoole_process::wait();
                    if ($ret['code'] === 0)
                    {
                        $output[] = "start UploadServer success";
                    }
                    else
                    {
                        $output[] = "start UploadServer failed";
                    }
                    break;
                case 'stop_service':
                    $this->stop($return['data']['s']);
                    break;
            }
        }
    }

}