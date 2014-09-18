<?php
namespace Sky;

class Daemon
{
    public $config;
    public $daemons;

    public function __construct($config)
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
                            $this->daemons[$pid]= $cc;
                        }
                        else
                        {
                            $worker = new \swoole_process(array($this, 'startUploadServer'), false, true);
                            $pid = $worker->start();
                            $this->daemons[$pid]= $cc;
                            $workers[$pid] = $worker;
                            echo "start upload server on $pid \n";
                        }
                        break;
                }
            }
            while(count($workers) > 0)
            {
                $ret = \swoole_process::wait();
                unset($workers[$ret['pid']]);
                echo ("worker[{$ret['pid']}] finish \n");
            }
        }
    }

    function startUploadServer(\swoole_process $worker)
    {
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
        if (!empty($this->config[$name]['pid']) && is_file($this->config[$name]['pid']))
        {
            unlink($this->config[$name]['pid']);
            echo "delete {$this->config[$name]['pid']}";
            $pid = file_get_contents($this->config[$name]['pid']);
            exec("kill -15 $pid");
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
            if (is_file($cc['pid']))
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


}