<?php
namespace Sky;

interface IService
{
    function onReceive($server, $fd, $from_id,$_data);
}