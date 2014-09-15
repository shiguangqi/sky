<?php
namespace Sky\Log;

class Stdout extends \Sky\Log
{
    public function log($msg, $level = self::INFO)
    {
        $msg = $this->format($msg, $level);
        echo $msg;
    }
}
