<?php

namespace Container;

class Project
{
    public $c;

    static $log_ext = '.log';
    static $pid_file_ext = '.pid';

    public function __construct($name,$container)
    {
        $this->c = $container;
        $this->config = $this->c->config;
        $this->c->sw->pconfig = new \Swoole\Config();
        $this->setConfigath($this->config['container']['project'][$name]['server']['document_root'].'/configs');
        $this->initLog($name);
        $this->initD($name);
        $this->initPid($name);
    }

    public function setConfigath($dir)
    {
        if (get_cfg_var('env.name') == 'dev')
        {
            $this->c->sw->pconfig->setPath($dir.'/dev');
        }
        else
        {
            $this->c->sw->pconfig->setPath($dir);
        }
    }

    public function initLog($name)
    {
        if (empty($this->config['project'][$name]['log_path']))
        {
            $path = CROOT.'/log';
        }
        else
        {
            $path = $this->config['project'][$name]['log_path'];
        }
        if (!is_dir($path))
        {
            mkdir($path,0777,true);

        }
        $file = $path.'/'.$name.self::$log_ext;
        $this->log = new \Swoole\Log\FileLog($file);
    }

    public function initPid($name)
    {
        if (empty($this->config['project'][$name]['pid_path']))
        {
            $path = CROOT.'/run';
        }
        else
        {
            $path = $this->config['project'][$name]['pid_path'];
        }
        if (!is_dir($path))
        {
            mkdir($path,0777,true);

        }
        $file = $path.'/'.$name.self::$pid_file_ext;
        $this->pid_file = $file;
    }

    public function initD($name)
    {
        if (empty($this->config['project'][$name]['init_path']))
        {
            $path = CROOT.'/init.d';
        }
        else
        {
            $path = $this->config['project'][$name]['init_path'];
        }
        if (!is_dir($path))
        {
            mkdir($path,0777,true);

        }
        $file = $path.'/'.$name;
        $this->init = $file;
    }
}