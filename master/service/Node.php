<?php
namespace Sky\Service;
/*
 * 服务其控制指令
 */

class Node extends \Sky\Service implements \Sky\IService
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
            case 'addnode':
                $this->addNode($server, $fd, $from_id);
                break;
            case 'delnode':
                $this->delNode($server, $fd, $from_id);
                break;
            case 'addname':
                $this->addName($server, $fd, $from_id,$data['params']);
                break;
            case 'getnode':
                $this->getNode($server, $fd, $from_id,$data['params']);
                break;
            case 'getallnode':
                $this->getAllNode($server, $fd, $from_id,$data['params']);
                break;
            case 'setgroup':
                $this->setGroup($server, $fd, $from_id,$data['params']);
                break;
            case 'getgroup':
                $this->getGroup($server, $fd, $from_id,$data['params']);
                break;
            case 'getallgroup':
                $this->getAllGroup($server, $fd, $from_id,$data['params']);
                break;
            case 'webinit':
                $this->webInit($server, $fd, $from_id,$data['params']);
                break;
            default:
                $this->error($fd, 9002,$data);
                break;
        }
    }

    public function getNode($server, $fd, $from_id,$params)
    {
        $this->getConnections($server);//获取其他worker节点信息 交叉获取

        if (!empty($params['n']))
        {
            $this->send($fd, $this->sky->nodes[$fd]);
        }
        else
        {
            $this->error($fd, 9003,$params);
        }
    }

    public function getAllNode($server, $fd, $from_id,$params)
    {
        $this->getConnections($server);//获取其他worker节点信息 交叉获取
        $this->send($fd, $this->sky->nodes);
    }

    //下发实体节点
    public function webInit($server, $fd, $from_id,$params)
    {
        //$this->getConnections($server);//获取其他worker节点信息 交叉获取
        //$this->res->setCmd('webinit');
        $return = array('node'=>$this->sky->nodes,'params'=>$params);
        $this->send($fd, $return);
    }

    /*
     * 为节点设置分组
     */
    public function setGroup($server, $fd, $from_id,$params)
    {
        if (!empty($params['g']) and !empty($params['n']))
        {
            $this->getConnections($server);//获取其他worker节点信息 交叉获取
            if (isset($this->sky->nodes[$fd]) and !empty($this->sky->nodes[$fd]))
            {
                $gid = $params['g'];
                $this->sky->nodes[$fd]['group'] = $gid;
                $this->sky->groups[$gid][$fd] = $this->sky->nodes[$fd];
            }
            else
            {
                $this->error($fd, 9005,$params);
            }
        }
        else
        {
            $this->error($fd, 9003,$params);
        }
    }

    public function getGroup($server, $fd, $from_id,$params)
    {
        if (!empty($params['g']))
        {
            $this->send($fd, $this->sky->groups[$params['g']]);
        }
        else
        {
            $this->error($fd, 9003,$params);
        }

    }

    public function getAllGroup($server, $fd, $from_id,$params)
    {
        $this->send($fd, $this->sky->groups);
    }

    public function addNode($server, $fd, $from_id)
    {
        $info = $server->connection_info($fd);
        $node_info['host'] = $info['remote_ip'];
        $node_info['port'] = $info['remote_port'];
        $node_info['connect_time'] = $info['connect_time'];
        $node_info['last_time'] = $info['last_time'];
        $node_info['group'] = 0;//默认分组为0
        $node_info['fd'] = $fd;
        $this->log("node info:".print_r($info,1));
        if ($info['from_port'] == $this->sky->port)//实体节点
        {
            $this->sky->nodes[$fd] = $node_info;
            //主动通知控制节点
            if (!empty($this->sky->ctl))
            {
                foreach ($this->sky->ctl as $f => $info)
                {
                    $this->send($f,$this->sky->nodes[$fd]);
                }
            }
        }
        elseif ($info['from_port'] == $this->sky->ctl_port)//控制节点
        {
            $this->sky->ctl[$fd] = $node_info;
        }
        $this->log("add node nodes:".print_r($this->sky->nodes,1));
        $this->log("add node ctls:".print_r($this->sky->ctl,1));
    }
    public function delNode($server,$fd, $from_id)
    {
        //若下线为实体节点 通知控制节点下线动作
        if (isset($this->sky->nodes[$fd]) and !empty($this->sky->nodes[$fd]))
        {
            if (!empty($this->sky->ctl))
            {
                foreach ($this->sky->ctl as $f => $info)
                {
                    $this->send($f,$this->sky->nodes[$fd]);
                }
            }
            unset($this->sky->nodes[$fd]);
        }
        if (isset($this->sky->ctl[$fd]) and !empty($this->sky->ctl[$fd]))
        {
            unset($this->sky->ctl[$fd]);
        }
        $this->log("del node nodes:".print_r($this->sky->nodes,1));
        $this->log("del node ctls:".print_r($this->sky->ctl,1));
    }

    //onc
    public function addName($server, $fd, $from_id,$params)
    {

        if (isset($this->sky->nodes[$fd]) and !empty($this->sky->nodes[$fd]))
        {
            $this->sky->nodes[$fd]['name'] = $params['n'];
        }
        if (!empty($this->sky->ctl))
        {
            foreach ($this->sky->ctl as $f => $node)
            {
                $this->send($f,$this->sky->nodes[$fd]);
            }
        }
        $this->log("add name ".print_r($this->sky->nodes,1));
    }
    /*
     * 支持多worker
     * 刷新其他worker的连接到当前节点
     */
//    public function getConnections($server)
//    {
//        $fds = $server->connection_list(0, 50);//暂定50个节点
//        if ($fds)
//        {
//            foreach ($fds as $fd)
//            {
//                $this->addNode($server,$fd);
//            }
//        }
//    }
}