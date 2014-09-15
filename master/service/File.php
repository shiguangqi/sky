<?php
namespace Sky\Service;

class File extends \Sky\Service implements \Sky\IService
{
    public $path;
    public $time;
    public $port;
    public function __construct($sky)
    {
        parent::__construct($sky);
        $this->time = !empty($this->sky->file['time'])?$this->sky->file['time']:30;
        $this->port = !empty($this->sky->file['port'])?$this->sky->file['port']:9507;
        $this->path = !empty($this->sky->file['path'])?$this->sky->file['path']:__DIR__."/../upload";
    }

    public function onReceive($server, $fd, $from_id,$data)
    {
        $this->service = strtolower($data['service']);
        $this->cmd = strtolower($data['cmd']);
        $this->setRes($this->service,$this->cmd);
        switch ($this->cmd)
        {
            case 'sendnode':
                $this->sendNode($server, $fd, $from_id,$data['params']);
                break;
            case 'sendnodes':
                $this->sendNodes($server, $fd, $from_id,$data['params']);
                break;
            case 'sendgroup':
                $this->sendGroup($server, $fd, $from_id,$data['params']);
                break;
            default:
                $return['msg'] = "命令不存在\n";
                $return['params']['c'] = $data['c'];
                $this->send($fd,$return);//命令错误
                break;
        }
    }

    /*
     * 给一个节点发送文件
     */
    public function sendNode($server, $fd, $from_id,$params)
    {
        if (!empty($params['n']) and !empty($params['f']))
        {
            $node = $params['n'];
            if (array_key_exists($node,$this->sky->nodes))
            {
                $params['h'] = $this->sky->nodes[$node]['host'];
                if (!is_file($params['f']))
                {
                    if (!empty($params['p']))
                    {
                        $params['f'] = $this->path.'/'.$params['p'].'/'.$params['f'];
                    }
                    else
                    {
                        $params['f'] = $this->path.'/'.$params['f'];
                    }
                }
                $this->uploadFile($server, $fd, $from_id, $params);
            }
            else
            {
                $return['msg'] = "节点不存在\n";
                $return['params']['c'] = $params['c'];
                $this->send($fd,$return);
            }
        }
        else
        {
            $return['msg'] = "参数错误\n";
            $return['params']['c'] = $params['c'];
            $this->send($fd,$return);
        }
    }

    /*
     * 给多个节点发送文件
     */

    public function sendNodes($server, $fd, $from_id,$params)
    {
        if (!empty($params['n']) and !empty($params['f']))
        {
            $nodes = explode('|',$params['n']);
            foreach ($nodes as $node)
            {
                if (empty($params['n']))
                    continue;
                if (array_key_exists($node,$this->sky->nodes))
                {
                    $params['h'] = $this->sky->nodes[$node]['host'];
                    if (!is_file($params['f']))
                    {
                        if (!empty($params['p']))
                        {
                            $params['f'] = $this->path.'/'.$params['p'].'/'.$params['f'];
                        }
                        else
                        {
                            $params['f'] = $this->path.'/'.$params['f'];
                        }
                    }
                    $this->uploadFile($server, $fd, $from_id, $params);
                }
                else
                {
                    $return['msg'] = "节点不存在\n";
                    $return['params']['c'] = $params['c'];
                    $this->send($fd,$return);
                }
            }
        }
        else
        {
            $return['msg'] = "参数错误\n";
            $return['params']['c'] = $params['c'];
            $this->send($fd,$return);
        }
    }
    /*
 * 向一组服务发送文件
 */
    public function sendGroup($server, $fd, $from_id,$params)
    {
        if ($gid = $this->checkGroup($params))// group id
        {
            if (isset($this->sky->groups[$gid]) and !empty ($this->sky->groups[$gid]))
            {
                foreach ($this->sky->groups[$gid] as $g)
                {
                    $g['h'] = $g['host'];//node中节点hosts
                    if (!is_file($params['f']))
                    {
                        $params['f'] = $this->path.'/'.$params['f'];
                    }
                    $g['f'] = $params['f']; //传入参数 f
                    $this->send($fd,"Node {$g['h']} start ---\n");
                    $this->uploadFile($server, $fd, $from_id, $g);
                }
            }
        }
        else
        {
            $return['msg'] = "参数错误\n";
            $return['params']['c'] = $params['c'];
            $this->send($fd,$return);
        }
    }

    public function uploadFile($server, $fd, $from_id,$params)
    {
        $return['params']['c'] = $params['c'];
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $file = $params['f'];
        $size = filesize($file);
        if (!is_file($file))
        {
            $return['msg'] = "Error: file '{$file}' not found\n";
            $this->send($fd,$return);
            return;
        }

        if (isset($params['t']))
        {
            $this->time = intval($params['t']);
        }

        if (!$client->connect($params['h'], $this->port ,$this->time, 0)) {
            $return['msg'] = "Error: connect to server {$params['h']} failed. \n" . swoole_strerror($client->errCode);
            $this->send($fd, $return);
            return;
        }
        $data = array(
            'name' => basename($file),
            'size' => $size,
        );

        if (!$client->send(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n")) {
            $return['msg'] = "Error: send header failed.\n";
            $this->send($fd, $return);
            return;
        }
        $msg = $this->getResponse($client);
        $return['msg'] = $msg;
        $this->send($fd, $return);
        $return['msg'] = "Start transport. file={$file}, size={$size}\n";
        $this->send($fd, $return);
        $fp = fopen($file, 'r');
        if (!$fp) {
            $this->send($fd, "Error: open $file failed.\n");
        }
        while(!feof($fp))
        {
            $read = fread($fp, 8000);
            if (!$client->send($read)) {
                $return['msg'] = "Start transport. file={$file}, size={$size}\n";
                $this->send($fd, "send failed. ErrCode=".$client->errCode."\n");
                break;
            }
        }
        $msg = $this->getResponse($client);
        $return['msg'] = $msg;
        $this->send($fd, $return);
        $return['msg'] = "Success. send_size = $size\n";
        $this->send($fd, $return);
    }

    public function checkParams($params)
    {
        if (!empty($params['h']) and !empty($params['p']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function checkGroup($params)
    {
        if (!empty($params['g']) and !empty($params['f'] ))
        {
            return $params['g'];
        }
        else
        {
            return false;
        }
    }


    public function getResponse(\swoole_client $client)
    {
        $recv = $client->recv();
        if (!$recv) {
            return ("Error: recv header failed.\n");
        }
        $respCode = json_decode($recv, true);
        if (!$respCode) {
            return ("Error: header json_decode failed.\n");
        }
        if ($respCode['code'] != 0) {
            return ("Server: message={$respCode['msg']}.\n");
        } else
            return "[FromServer]\t{$respCode['msg']}\n";
    }
}