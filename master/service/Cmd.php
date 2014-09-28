<?php
namespace Sky\Service;
/*
 * 向node发送指令服务
 */

class Cmd extends \Sky\Service implements \Sky\IService
{
    public function __construct($sky)
    {
        parent::__construct($sky);
    }

    public function onReceive($server, $fd, $from_id,$data)
    {
        $this->handler($server, $fd, $from_id,$data);
    }

    public function handler($server, $fd, $from_id,$data)
    {
        $this->service = strtolower($data['service']);
        $this->cmd = strtolower($data['cmd']);
        $this->setRes($this->service,$this->cmd);
        switch ($this->cmd)
        {
            case 'start_service':
                $this->start_service($server, $fd, $from_id,$data['params']);
                break;
            case 'stop_service':
                $this->stop_service($server, $fd, $from_id,$data['params']);
                break;
            case 'install':
                $this->install($server, $fd, $from_id,$data['params']);
                break;
            default:
                $return['msg'] = "命令不存在\n";
                $return['params']['c'] = $data['c'];
                $this->send($fd,$return);//命令错误
                break;
        }
    }

    public function start_service($server, $fd, $from_id,$params)
    {
        if (!empty($params['sn']) and !empty($params['s']))
        {
            $node = $params['sn'];
            if (array_key_exists($node,$this->sky->nodes))
            {
                $return['s'] = $params['s'];
                $this->send($node,$return);
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

    public function stop_service($server, $fd, $from_id,$params)
    {
        if (!empty($params['sn']) and !empty($params['s']))
        {
            $node = $params['sn'];
            if (array_key_exists($node,$this->sky->nodes))
            {
                $return['s'] = $params['s'];
                $this->send($node,$return);
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

    //master upload完成触发  模拟pre_install 前置脚本执行
    public function file_install($server, $fd, $from_id,$params)
    {
        $this->setRes('cmd','file_install');
        $node_fd = $this->getNodeByIp($params['h']);
        $file = basename($params['f']);
        $return['f'] =  $file; //上传文件的文件名
        $return['fd'] =  $fd;//需要带上控制节点的fd,response 下次通信用
        $return['c'] =  $params['c']; //client id
        $this->send($node_fd, $return);
    }

    //node 节点返回安装
    public function install($server, $fd, $from_id,$params)
    {
        if (!empty($params['c']))//返回状态日后可以选择从web客户端启动 也按照状态直接启动
        {
            $ctl_fd = $params['fd'];
            $return['c'] = $params['c'];
            $return['s'] = $params['s'];
            $return['o'] = $params['o'];
            $return['f'] = $params['f'];
            $return['fd'] = $params['fd'];
            $this->send($ctl_fd, array('params'=>$return));
        }
    }

}