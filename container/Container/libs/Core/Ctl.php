<?php
/**
 * Time: 下午2:49
 * 控制容器app
 */
namespace Container;

class Ctl
{
    static public  $app_path;
    public $container;//container

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function load($app)
    {

    }

    public function reload($app)
    {

    }

    public function unload($app)
    {

    }
}