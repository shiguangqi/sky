<?php
namespace Sky;

class Node
{
    public $client;
    public $client_handler;
    public $node_name;
    static public $node;

    public $server;
    protected $worker_id;
    public $config;
    protected $setting;//swoole setting

    public $loger;
    public $daemon;
    public $cmd;
    public $container;

    public $pid_file;

    static function getInstance()
    {
        if (!self::$node)
        {
            self::$node = new Node();
        }
        return self::$node;
    }

    function onTimer(\swoole_server $server, $interval)
    {
        call_user_func(array($this->client_handler,"clientTimer"),$this->client);
    }

    function onStart(\swoole_server $server, $worker_id)
    {
        $this->log("node worker start");
        global $argv;
        \Swoole\Console::setProcessName("{$argv[0]} [node server] : worker");

        $this->worker_id = $worker_id;
        $this->client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_ASYNC);
        $this->client_handler = new \Sky\ClientHandler($this);
        $this->client->on("connect", array($this->client_handler,"clientConnect"));
        $this->client->on("receive", array($this->client_handler,"clientReceive"));
        $this->client->on("close", array($this->client_handler,"clientClose"));
        $this->client->on("error", array($this->client_handler,"clientError"));
        $this->client->connect($this->config['master']['host'], $this->config['master']['port'],1,1);
        $server->addtimer($this->config['node']['heartbeat']);
    }

    function onWorkerStop(\swoole_server $server, $worker_id)
    {

    }

    function onReceive($server, $fd, $from_id, $data)
    {
        return;
    }

    public function setLoger($log)
    {
        $this->loger = $log;
    }

    public function log($msg)
    {
        $this->loger->put($msg);
    }

    function init($config)
    {
        $this->config = $config;
        $this->pid_file = $config['node']['pid'];
        $this->setting = $this->config['swoole'];
        $this->server = new \swoole_server($config['node']['host'], $config['node']['port'], SWOOLE_PROCESS, SWOOLE_TCP);

        $this->node_name = $config['node']['name'];
        $this->daemon = new \Sky\Daemon($config['daemon'],$this);
        //$this->monitor = new \Sky\Monitor($config['monitor'],$this);
        //if (isset($config['monitor']) and !empty($config['monitor']))
        $this->cmd = new \Sky\Cmd($config['monitor'],$this);
        if (isset($config['protocol']) and !empty($config['protocol']))
            $this->container = new \Sky\Cmd($config['protocol'],$this);
    }

    function onMasterStart($server)
    {
        $this->log("node server start");
        global $argv;
        \Swoole\Console::setProcessName("{$argv[0]} [node server] : master -host= {$this->config['node']['host']} -port={$this->config['node']['port']}");
        file_put_contents($this->pid_file,$server->master_pid);
    }

    function onManagerStart($server)
    {
        global $argv;
        \Swoole\Console::setProcessName("{$argv[0]} [node server] : manager");
    }

    function onShutdown($server)
    {
        $this->log("node server shutdown");
        unlink($this->pid_file);
    }

    function run($setting=array())
    {
        $_setting = array_merge($this->setting, $setting);
        $this->server->set($_setting);
        $this->server->on('Start', array($this, 'onMasterStart'));
        $this->server->on('Shutdown', array($this, 'onShutdown'));
        $this->server->on('ManagerStart', array($this, 'onManagerStart'));
        $this->server->on('workerStart', array($this, 'onStart'));
        $this->server->on('WorkerStop', array($this, 'onWorkerStop'));
        $this->server->on('receive', array($this, 'onReceive'));
        $this->server->on('timer', array($this, 'onTimer'));
        $this->server->start();
    }
}