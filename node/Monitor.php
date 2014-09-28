<?php
namespace Sky;

class Monitor
{
    public $config;
    public $node;

    public function __construct($config,$node)
    {
        if (!empty($config))
        {
            $this->config = $config;
        }
        $this->node = $node;
    }

    public function getMonitors()
    {
        foreach ($this->config as $name => $cc)
        {
            //优先检查pid 后面 增加按照配置进程名称去查找 支持有的系统没有写入pid
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

    public function start()
    {

    }

    public function stop()
    {

    }

    public function restart()
    {

    }

    public function reload()
    {

    }
}