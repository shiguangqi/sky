<?php
namespace Sky;

class Log
{
    static $date_format = '[Y-m-d H:i:s]';

    const TRACE   = 0;
    const INFO    = 1;
    const NOTICE  = 2;
    const WARN    = 3;
    const ERROR   = 4;

    static public $level = array(
        'TRACE',
        'INFO',
        'NOTICE',
        'WARN',
        'ERROR',
    );

    function format($msg,$l)
    {
        $level = self::$level[$l];
        return date(self::$date_format)."\t{$level}\t{$msg}\n";
    }
}