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
        //暂时不区分daemon 服务和命令服务 一个入口
        $lines = explode("\r\n",$data);
        foreach ($lines as $line)
        {
            if (empty($line))
            {
                continue;
            }
            $data = json_decode($data,1);
            if (in_array($data['cmd'],array('start_service','stop_service')))
            {
                call_user_func(array($this->node->daemon,"cmd"),$data);
            }
            else //指令服务
            {
                call_user_func(array($this->node->cmd,"dispatch"),array('client'=>$client,'content'=>$data));
            }
        }


    }

    function clientClose($client)
    {
        $this->node->log("clientClose passive");
        $this->node->server->shutdown();
    }

    function clientError($client)
    {
        echo "clientError\n";
    }

    function clientTimer(\swoole_client $client)
    {
        $client->send($this->timer_header.$this->getNodeBit().$this->protocol_end);
    }

    function getNodeBit()
    {
        $daemons = $this->node->daemon->getDaemons();
        if (!empty($this->node->cmd))
        {
            $monitors = $this->node->cmd->getMonitors();
        }
        $data['daemon'] = $daemons;
        $data['monitor'] = $monitors;
        $str = '';
        if (!empty($data))
        {
            $str = json_encode($data);
        }
        return " -d $str";
    }
}