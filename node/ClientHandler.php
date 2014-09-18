<?php
namespace sky;

class ClientHandler
{
    public $node;
    public $config;

    private $timer_header = "heart bit ";
    private $name_header = "node addname ";
    private $protocol_end = "\r\n";

    public function __construct($node)
    {
        $this->node = $node;
        $this->config = $this->node->config;
    }

    function clientConnect($client)
    {
        echo "clientConnect \n";
        //发送节点名称给master供后台展示
        $client->send($this->name_header."-n {$this->node->node_name}".$this->protocol_end);
    }

    function clientReceive($client, $data)
    {
        echo "received: $data\n";
    }

    function clientClose($client)
    {
        echo "clientClose\n";
    }

    function clientError($client)
    {
        echo "clientError\n";
    }

    function clientTimer(\swoole_client $client)
    {
        echo $this->timer_header.$this->getNodeDaemon().$this->protocol_end;
        $client->send($this->timer_header.$this->getNodeDaemon().$this->protocol_end);
    }

    function getNodeDaemon()
    {
        $daemons = $this->node->daemon->getDaemons();
        $str = '';
        if (!empty($daemons))
        {
            $str = json_encode($daemons);
        }
        return " -d $str";
    }
}