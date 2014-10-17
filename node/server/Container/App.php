<?php
/**
 * Created by PhpStorm.
 * User: shiguangqi
 * Date: 14-10-15
 * Time: 下午2:53
 */

namespace Sky\Container;

class App
{
    public $name;
    public $procotol;

    /*
     * 运行监控
     */
    public $last_start_time;
    public $last_end_time;

    /*
     * 1 运行中
     * 0 停止
     */
    public $status;

}