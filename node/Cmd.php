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

    public function __construct($node)
    {
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
                case 'install':
                    $main = $data['data'];
                    $ctl_fd = $main['c'];
                    $file = $main['f']; //文件名称
                    exec(__DIR__."/install.sh {$file}",$output,$return);
                    var_dump($return);
                    if ($return === 0)
                    {
                        $output[] = 'install success';
                        $client->send($this->buildInstallMsg($params,$output));
                    }
                    else
                    {
                        $client->send($this->buildInstallMsg($params,$output));
                    }
                    break;
            }
        }
    }

    public function buildInstallMsg($params,$output)
    {
        $o = json_encode($output);
        $data = $params['content'];
        $line = $this->cmd_header."rep_install -f {$data['data']['f']} -c {$data['data']['c']} -o $o ".$this->protocol_end;
        var_dump($line);
        return $line;
    }
}