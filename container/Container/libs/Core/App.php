<?php
namespace Container;

class App extends \Swoole\Controller
{
    protected $config;
    protected $pconfig;

    public function __construct(\Swoole $swoole)
    {
        parent::__construct($swoole);
        $this->config = new \Swoole\Config();
        $swoole->config = $this->config;
        if (get_cfg_var('env.name') == 'dev')
        {
            $this->config->setPath(\Swoole::$app_path.'/configs/dev');
        }
        else
        {
            $this->config->setPath(\Swoole::$app_path.'/configs');
        }
        $this->pconfig = $swoole->pconfig;
    }


    public function __destruct()
    {
        unset($this->config);
    }
}