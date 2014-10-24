<?php
/**
 * Created by PhpStorm.
 * User: shiguangqi
 * Date: 14-10-14
 * Time: 下午2:49
 */

class Container
{
    /**
     * @var
     * 外部库实例
     */
    public $sw;
    /**
     * @var
     * 容器实例
     */
    static public $c;
    public $status;
    public $ctl;

    /*
     * container属性
     * 和swoole属性加载方法一致
     */
    public $attr = array(
        'cache' => true,
        'config' => true, //缓存
        'log' => true, //日志
    );

    public function __construct($php)
    {
        $this->sw = $php;
    }

    static function getInstance($php)
    {
        if (!self::$c)
        {
            self::$c = new self($php);
        }
        return self::$c;
    }

    public function run()
    {
        $container = $this->config['container'];
        if ($container)
        {
            $projects = $container['project'];
            if (!empty($projects))
            {
                $workers = array();
                foreach ($projects as $name => $project)
                {

                    if ($project['auto_start'] == 1)
                    {
                        $this->initProject($name);
                        switch ($project['protocol'])
                        {
                            case 'http':
                                $pid_file = $this->$name->pid_file;
                                if (is_file($pid_file))
                                {
                                    $pid = file_get_contents($pid_file);
                                    $this->log->put("http server 已经在执行 pid=".$pid);
                                }
                                else
                                {
                                    $worker = new \swoole_process(array($this, 'startServer'), false, false);
                                    $worker->config = $project;
                                    $pid = $worker->start();
                                    $workers[$pid] = $worker;
                                    $this->log->put("start {$project['protocol']}-{$name} server ok");
                                }
                                $this->status[$name] = $pid;
                                break;
                        }
                    }
                    while(count($workers) > 0)
                    {
                        $ret = \swoole_process::wait();
                        unset($workers[$ret['pid']]);
                    }
                }
            }
            else
            {
                Swoole\Error::info("Container Error","container config wrong");
            }
        }
    }

    public function initProject($name)
    {
        $this->$name = new \Container\Project($name,$this);
    }

    public function startServer(\swoole_process $worker)
    {
        $config = $worker->config;
        $http = new \Container\Protocol\AppServer();
        $http->loadSetting($config);
        $name = $config['name'];
        $http->setLogger($this->$name->log);
        $server = new \Swoole\Network\Server($config['host'],$config['port'],$config['enable_ssl']);
        $server->setProtocol($http);
        //$config['swoole']['pid_file'] = $this->$name->pid_file;
        $server->run($config['swoole']);
    }

    public function stopServer($name)
    {
        $this->status[$name] = 0;
        $init = $this->config[$name]['init'];
        exec($init." _stop",$output,$return);
    }

    function __get($attr)
    {
        if (in_array($attr,$this->attr) and empty($this->$attr))
        {
            $this->$attr = $this->sw->$attr;
            return $this->$attr;
        }
        return $this->$attr;
    }
}