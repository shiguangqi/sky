<?php
namespace App;


class WebsocketSvr extends \Swoole\Network\Protocol\WebSocket
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
        $this->client = new \swoole_client(SWOOLE_TCP | SWOOLE_KEEP, SWOOLE_SOCK_ASYNC);
        $this->client->on("connect", array($this,"clientConnect"));
        $this->client->on("receive", array($this,"clientReceive"));
        $this->client->on("close", array($this,"clientClose"));
        $this->client->on("error", array($this,"clientError"));
        $this->client->connect(SKY_HOST, SKY_PORT);
    }


    function clientConnect($cli)
    {
        echo "clientConnect \n";
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
            print_r($return);
            if (($return['service'] == 'node' && ($return['cmd'] == 'addnode' || $return['cmd'] == 'delnode'))
                || ($return['service'] == 'heart' && $return['cmd'] == 'send')
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
                unset($return['data']['params']);
                $this->sendJson($fd,$return);
            }
        }
    }

    function clientClose($cli)
    {
        echo "clientClose\n";
    }

    function clientError($cli)
    {
        echo "clientError\n";
    }

    function onMessage($client_id, $ws)
    {
        $msg = json_decode($ws['message'], true);
        print_r($msg);
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
        $line .= ' -c '.$msg['c'];//client_id
        $line .= $this->eol;
        print_r($line);
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