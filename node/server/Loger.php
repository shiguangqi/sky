<?php
namespace Sky;
require __DIR__.'/log/Log.php';
require __DIR__.'/log/Stdout.php';
require __DIR__.'/log/File.php';
class Loger
{
    static public $log;

    static function getLoger($config)
    {
        if (!self::$log)
        {
            if ($config['type'] == 'stdout')
            {
                self::$log = new \Sky\Log\Stdout();
            }
            else
            {
                self::$log = new \Sky\Log\File($config['file']);
            }
        }
        return self::$log;
    }
}