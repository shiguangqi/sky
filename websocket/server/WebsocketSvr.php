<?php
namespace Sky;


class WebsocketSvr extends \Swoole\Protocol\WebSocket
{
    public $config;
    public $client;
    public $meta;

    private $eol = "\r\n";

    function __construct($config = array())
    {
        parent::__construct($config);
    }

    function onStart($server)
    {
        global $argv;
        \Swoole\Console::setProcessName("php $argv[0] : worker");

        $this->client = new \swoole_client(SWOOLE_TCP | SWOOLE_KEEP, SWOOLE_SOCK_ASYNC);
        $this->client->on("connect", array($this,"clientConnect"));
        $this->client->on("receive", array($this,"clientReceive"));
        $this->client->on("close", array($this,"clientClose"));
        $this->client->on("error", array($this,"clientError"));
        $this->client->connect($this->server->swooleSetting['sky_host'], $this->server->swooleSetting['sky_port']);
    }


    function clientConnect($cli)
    {
        $this->log("clientConnect");
    }

    function clientReceive($cli, $data)
    {
        $lines = explode("\r\n",$data);
        foreach ($lines as $line)
        {
            if (empty($line))
            {
                continue;
            }
            $return = json_decode($line,1);
            if ($return['service'] == 'cmd')
            {
                $this->log(print_r($return,1));
            }
            if (($return['service'] == 'node' && ($return['cmd'] == 'addnode' || $return['cmd'] == 'delnode' || $return['cmd'] == 'addname'))
                || ($return['service'] == 'heart' && $return['cmd'] == 'bit')
                )
            {
                if (!empty($this->connections))
                {
                    foreach ($this->connections as $fd => $info)
                    {
                        $this->sendJson($fd,$return);
                    }
                }
            }
            else
            {
                $fd = $return['data']['params']['c'];
                //unset($return['data']['params']);
                $this->sendJson($fd,$return);
            }
        }
    }

    function clientClose($cli)
    {
        $this->server->shutdown();
        echo "clientClose\n";
    }

    function clientError($cli)
    {
        echo "clientError\n";
    }

    function onMessage($client_id, $ws)
    {
        $msg = json_decode($ws['message'], true);
        if (empty($msg['service']))
        {
            $this->sendErrorMessage($client_id,"服务错误");
            return;
        }
        $msg['c'] = $client_id;
        $this->client->send($this->pack($msg));
    }

    function pack($msg)
    {
        $this->log(print_r($msg,1));
        $line = '';
        $line .= $msg['service'].' '.$msg['cmd'];
        if (!empty($msg['n']))
        {
            $line .= ' -n '.implode('|',$msg['n']);
        }
        if (!empty($msg['g']))
        {
            $line .= ' -g '.$msg['g'];
        }
        if (!empty($msg['f']))
        {
            $line .= ' -f '.$msg['f'];
        }
        if (!empty($msg['p']))
        {
            $line .= ' -p '.$msg['p'];
        }
        if (!empty($msg['sn']))
        {
            $line .= ' -sn '.$msg['sn'];
        }
        if (!empty($msg['s']))
        {
            $line .= ' -s '.$msg['s'];
        }
        if (!empty($msg['m']))
        {
            $line .= ' -m '.$msg['m'];
        }
        $line .= ' -c '.$msg['c'];//client_id
        $line .= $this->eol;
        $this->log(print_r($line,1));
        return $line;
    }
    /**
     * 发送错误信息
     * @param $client_id
     * @param $msg
     */
    function sendErrorMessage($client_id, $msg)
    {
        $this->sendJson($client_id, array('service' => 'error', 'msg' => $msg));
    }

    /**
     * 发送JSON数据
     * @param $client_id
     * @param $array
     */
    function sendJson($client_id, $array)
    {
        $msg = json_encode($array);
        $this->send($client_id, $msg);
    }

    /**
     * 广播JSON数据
     * @param $client_id
     * @param $array
     */
    function broadcastJson($client_id, $array)
    {
        $msg = json_encode($array);
        $this->broadcast($client_id, $msg);
    }

    function broadcast($client_id, $msg)
    {
        if (extension_loaded('swoole'))
        {
            $sw_serv = $this->getSwooleServer();
            $start_fd = 0;
            while(true)
            {
                $conn_list = $sw_serv->connection_list($start_fd, 10);
                if($conn_list === false)
                {
                    break;
                }
                $start_fd = end($conn_list);
                foreach($conn_list as $fd)
                {
                    if($fd === $client_id) continue;
                    $this->send($fd, $msg);
                }
            }
        }
        else
        {
            foreach ($this->connections as $fd => $info)
            {
                if ($client_id != $fd)
                {
                    $this->send($fd, $msg);
                }
            }
        }
    }
}