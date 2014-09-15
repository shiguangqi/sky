<?php
namespace Sky;

class Node
{
    protected $server;
    protected $worker_id;
    protected $config;

    public $client;
    static public $node;

    private $protocol_header = "heart send ";
    private $protocol_end = "\r\n";

    public function __construct()
    {

    }

    static function getInstance()
    {
        if (!self::$node)
        {
            self::$node = new Node();
        }
        return self::$node;
    }

    public function init($host, $port)
    {

    }

    function onTimer(\swoole_server $serv, $interval)
    {
        //向master发送心跳包
        $this->client->send($this->protocol_header.$this->protocol_end);
    }
    public function onReceive()
    {

    }

    function onFinish()
    {

    }

    function onStart(\swoole_server $server, $worker_id)
    {
        $this->worker_id = $worker_id;
        if ($worker_id == 0)
        {
            $this->client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_ASYNC);
            $this->client->on("connect", array($this,"clientConnect"));
            $this->client->on("receive", array($this,"clientReceive"));
            $this->client->on("close", array($this,"clientClose"));
            $this->client->on("error", array($this,"clientError"));
            $this->client->connect($this->config['master']['host'], $this->config['master']['port'],1,1);
            $server->addtimer($this->config['node']['heartbeat']);
        }
    }

    function clientConnect($cli)
    {
        echo "clientConnect \n";
    }

    function clientReceive($cli, $data)
    {
        echo "received: $data\n";
    }

    function clientClose($cli)
    {
        echo "clientClose\n";
    }

    function clientError($cli)
    {
        echo "clientError\n";
    }

    function run($config)
    {
        $this->config = $config;
        $_setting = $this->config['swoole'];
        $server = new \swoole_server($config['node']['host'], $config['node']['port'], SWOOLE_PROCESS, SWOOLE_TCP);
        $server->set($_setting);
        $server->on('workerStart', array($this, 'onStart'));
        $server->on('receive', array($this, 'onReceive'));
        $server->on('timer', array($this, 'onTimer'));
        $server->on('finish', array($this, 'onFinish'));
        $this->server = $server;
        $this->server->start();
    }
}