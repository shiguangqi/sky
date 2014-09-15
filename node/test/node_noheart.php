<?php

$cli = new swoole_client(SWOOLE_TCP | SWOOLE_KEEP);
$cli->connect("127.0.0.1",9999);
usleep(10000000);