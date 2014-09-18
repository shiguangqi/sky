<?php
namespace Sky\Service;

class Heart extends \Sky\Service implements \Sky\IService
{
    public function __construct($sky)
    {
        parent::__construct($sky);
    }

    public function onReceive($server, $fd, $from_id,$data)
    {
        $this->service = strtolower($data['service']);
        $this->cmd = strtolower($data['cmd']);
        $this->setRes($this->service,$this->cmd);
        switch ($this->cmd)
        {
            case 'bit':
                $this->bit($server, $fd, $from_id,$data['params']);
                break;
        }
    }


    public function bit($server, $fd, $from_id,$params)
    {
        if (!empty($this->sky->ctl))
        {
            $this->updateLastTime();
            if (isset($params['d']))
            {
                $daemons = json_decode($params['d'],1);
                if ($daemons)
                {
                    $this->sky->nodes[$fd]['daemon'] = $daemons;
                }
            }
            foreach ($this->sky->ctl as $f => $info)
            {
                $this->send($f,$this->sky->nodes);
            }
        }
    }

    public function updateLastTime()
    {
        if (!empty($this->sky->nodes))
        {
            foreach ($this->sky->nodes as $fd => $node)
            {
                $info = $this->sky->server->connection_info($fd);
                $this->sky->nodes[$fd]['last_time'] = $info['last_time'];
            }
        }
    }
}