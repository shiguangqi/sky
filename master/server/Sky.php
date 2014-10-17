<?php


class Sky
{
    public $nodes;
    public $ctl; //控制节点
    public $groups;
    public $server;//依赖的网络通信扩展
    public $white_list;

    public $serverSetting;
    public $config;

    public $service;
    public $dispatch;
    public $res;//response
    public $log;

    static public $sky;

    public $port;
    public $ctl_port;

    public $file;

    public $pid_file;
    public $pid;

    public function __construct()
    {

    }

    public function init($config)
    {
        $this->config = $config;
        $this->port = $config['server']['port'];
        $this->server = new \swoole_server($config['server']['host'], $config['server']['port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->pid_file = $this->config['server']['pid'];
        $this->server->sky = $this;
        $this->serverSetting = $config['swoole'];

        $this->addListener($config['ctl']['host'],$config['ctl']['port'],SWOOLE_SOCK_TCP);
        $this->ctl_port = $config['ctl']['port'];
        $this->file = $config['file'];

        $this->dispatch = new \Sky\Dispatch($this);
        $this->res = new \Sky\Response($this);
    }

    public function setLoger($log)
    {
        $this->log = $log;
    }

    public function log($msg)
    {
        $this->log->put($msg);
    }

    public function setWhiteList($ip_list)
    {
        if (is_array($ip_list) and !empty($ip_list))
        {
            foreach ($ip_list as $ip)
            {
                $this->white_list[$ip] = $ip;
            }
        }
    }

    static function getInstance()
    {
        if (!self::$sky)
        {
            self::$sky = new Sky();
        }
        return self::$sky;
    }

    public function onMasterStart($server)
    {
        global $argv;
        \Swoole\Console::setProcessName("{$argv[0]} [master server] : master -host= {$this->config['server']['host']} -port={$this->config['server']['port']}");
        $this->log("master server start");
        $this->pid = $server->master_pid;
        file_put_contents($this->pid_file,$this->pid);
    }

    function onManagerStop($server)
    {
        $this->log("master server stop");
        if (file_exists($this->pid_file))
            unlink($this->pid_file);
    }

    function onManagerStart($server)
    {
        global $argv;
        \Swoole\Console::setProcessName("$argv[0] [master server] : manager");
    }

    public function run($setting=array())
    {
        $set = array_merge($this->serverSetting, $setting);
        $this->server->set($set);
        $this->server->on('Start', array($this, 'onMasterStart'));
        $this->server->on('ManagerStart', array($this, 'onManagerStart'));
        $this->server->on('managerStop', array($this,'onManagerStop'));
        $this->server->on('WorkerStart', array($this->dispatch, 'onStart'));
        $this->server->on('Connect', array($this->dispatch, 'onConnect'));
        $this->server->on('Receive', array($this->dispatch, 'onReceive'));
        $this->server->on('Close', array($this->dispatch, 'onClose'));
        $this->server->on('WorkerStop', array($this->dispatch, 'onShutdown'));

        if (is_callable(array($this->dispatch, 'onTimer')))
        {
            $this->server->on('Timer', array($this->dispatch, 'onTimer'));
        }
        if (is_callable(array($this->dispatch, 'onTask')))
        {
            $this->server->on('Task', array($this->dispatch, 'onTask'));
            $this->server->on('Finish', array($this->dispatch, 'onFinish'));
        }
        $this->server->start();
    }

    public function shutDown()
    {
        return $this->server->shutdown();
    }

    public function close($client_id)
    {
        return $this->server->close($client_id);
    }

    public function addListener($host, $port, $type)
    {
        return $this->server->addlistener($host, $port, $type);
    }
}