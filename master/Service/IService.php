<?php
namespace Sky\Service;

interface IService
{
    function onReceive($server, $fd, $from_id,$_data);
}