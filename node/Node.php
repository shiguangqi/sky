<?php
namespace Sky;

require __DIR__."/ClientHandler.php";
require __DIR__."/Daemon.php";
class Node
{
    public $client;
    public $client_handler;
    public $node_name;
    static public $node;

    protected $server;
    protected $worker_id;
    public $config;
    protected $setting;//swoole setting

    public $loger;
    public $daemon;

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
        //$this->client->send($this->protocol_header.$this->protocol_end);
        call_user_func(array($this->client_handler,"clientTimer"),$this->client);
    }

    function onStart(\swoole_server $server, $worker_id)
    {
        global $argv;
        cli_set_process_title("{$argv[0]} [node server] : worker");

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

    function onReceive($server, $fd, $from_id, $data)
    {
        //empty
        return;
    }

    public function setLoger($log)
    {
        $this->loger = $log;
    }

    public function log($msg)
    {
        $this->loger->log($msg);
    }

    function init($config)
    {
        $this->config = $config;
        $this->setting = $this->config['swoole'];
        $this->server = new \swoole_server($config['node']['host'], $config['node']['port'], SWOOLE_PROCESS, SWOOLE_TCP);
        $this->node_name = $config['node']['name'];
        $this->daemon = new \Sky\Daemon($config['daemon']);
        $this->daemon->autostart();
    }

    function onMasterStart($server)
    {
        global $argv;
        cli_set_process_title("{$argv[0]} [node server] : master -host= {$this->config['node']['host']} -port={$this->config['node']['port']}");
    }

    function onManagerStart($server)
    {
        global $argv;
        cli_set_process_title("{$argv[0]} [node server] : manager");
    }

    function onShutdown($server)
    {

    }

    function run($setting=array())
    {
        $_setting = array_merge($this->setting, $setting);
        $this->server->set($_setting);
        $this->server->on('Start', array($this, 'onMasterStart'));
        $this->server->on('Shutdown', array($this, 'onShutdown'));
        $this->server->on('ManagerStart', array($this, 'onManagerStart'));
        $this->server->on('workerStart', array($this, 'onStart'));
        $this->server->on('receive', array($this, 'onReceive'));
        $this->server->on('timer', array($this, 'onTimer'));
        $this->server->start();
    }
}