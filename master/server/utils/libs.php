<?php

function setProcessName($name)
{
    if (function_exists('cli_set_process_title'))
    {
        cli_set_process_title($name);
    }
    else if(function_exists('swoole_set_process_name'))
    {
        swoole_set_process_name($name);
    }
    else
    {
        trigger_error(__METHOD__." failed. require cli_set_process_title or swoole_set_process_name.");
    }
}