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
                    $config[$k]['pid'] = ROOT.$c['pid'];
                }
                if (isset($c['log_file']))
                {
                    $config[$k]['log_file'] = ROOT.$c['log_file'];
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
                            echo "upload server 已经在执行 pid=".$pid."\n";
                        }
                        else
                        {
                            $worker = new \swoole_process(array($this, 'startUploadServer'), false, true);
                            $pid = $worker->start();
                            $workers[$pid] = $worker;
                            echo "start service upload server ok\n";
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
        $worker->exec("/usr/bin/php",array(__DIR__."/factory/upload_server.php"));
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
        if (!empty($this->config[$name]['pid']) )
        {
            if (file_exists($this->config[$name]['pid']))
            {
                unlink($this->config[$name]['pid']);
            }
            exec("ps -eaf |grep " . $name . " |grep -v grep |awk '{print $2}'|xargs kill -9");
            echo "stop service upload server ok\n";
        }
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
                    $workers = array();
                    $worker = new \swoole_process(array($this, 'startUploadServer'), false, true);
                    $pid = $worker->start();
                    $workers[$pid] = $worker;
                    echo "start service upload server ok\n";
                    while(count($workers) > 0)
                    {
                        $ret = \swoole_process::wait();
                        unset($workers[$ret['pid']]);
                    }
                    break;
                case 'stop_service':
                    $this->stop($return['data']['s']);
                    break;
            }
        }
    }

}