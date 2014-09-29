<?php
namespace Sky;
/*
 * node 节点指令处理
 */

class Cmd
{
    public $node;
    public $register_cmd = array('pre_install','post_install');//支持的指令
    public $upload_tmp_path = '/tmp';
    public $install_sh = 'install.sh';

    private $cmd_header = "cmd ";
    private $protocol_end = "\r\n";

    public function __construct($config,$node)
    {
        if (!empty($config))
        {
            $this->config = $config;
        }
        $this->node = $node;
    }

    public function dispatch($params)
    {
        $data = $params['content'];
        $client = $params['client'];
        if (!empty($data))
        {
            switch ($data['cmd'])
            {
                case 'file_install':
                    $file = $data['data']['f']; //文件名称
                    exec(__DIR__."/sh/init.sh {$file}",$output,$return);
                    if ($return === 0)
                    {
                        $output[] = 'install success';
                        $params['status'] = 0;
                    }
                    else
                    {
                        $output[] = 'install failed';
                        $params['status'] = 1;
                    }
                    $client->send($this->response($params,$output,'file_install'));
                    break;
                case 'start_monitor':
                    $this->start_monitor($params,$client);
                    break;
                case 'stop_monitor':
                    $this->stop_monitor($params,$client);
                    break;
            }
        }
    }

    public function response($params,$output,$type)
    {
        $o = implode("\n",$output);
        $data = $params['content'];
        switch ($type)
        {
            case 'file_install' :
                return $this->cmd_header."_{$type} -s {$params['status']} -f {$data['data']['f']} -fd {$data['data']['fd']} -c {$data['data']['c']} -o $o ".$this->protocol_end;
                break;
            case 'stop_monitor' :
            case 'start_monitor' :
                return $this->cmd_header."_{$type} -s {$params['status']} -m {$data['data']['m']} -fd {$data['data']['fd']} -c {$data['data']['c']} -o $o ".$this->protocol_end;
                break;
        }
    }

    public function start_monitor($params,$client)
    {
        $name = $params['content']['data']['m'];
        $init = $this->config[$name]['init'];
        exec($init." _start",$output,$return);
        var_dump($init." _start");
        var_dump($output);
        var_dump($return);
        if ($return === 0)
        {
            $output[] = "start {$name} success";
            $params['status'] = 0;
        }
        else
        {
            $output[] = "start {$name} failed";
            $params['status'] = 1;
        }
        $client->send($this->response($params,$output,'start_monitor'));
    }

    public function stop_monitor($params,$client)
    {
        $name = $params['content']['data']['m'];
        $init = $this->config[$name]['init'];
        exec($init." _stop",$output,$return);
        var_dump($init." _stop");
        var_dump($output);
        var_dump($return);
        if ($return === 0)
        {
            $output[] = "stop {$name} success";
            $params['status'] = 0;
        }
        else
        {
            $output[] = "stop {$name} failed";
            $params['status'] = 1;
        }
        $client->send($this->response($params,$output,'start_monitor'));
    }

    public function getMonitors()
    {
        foreach ($this->config as $name => $cc)
        {
            //优先检查pid 后面 增加按照配置进程名称去查找 支持有的系统没有写入pid
            if (file_exists($cc['pid']))
            {
                $this->config[$name]['_pid'] = file_get_contents($cc['pid']);
            }
            else
            {
                $this->config[$name]['_pid'] = 0;
            }
        }
        return $this->config;
    }
}