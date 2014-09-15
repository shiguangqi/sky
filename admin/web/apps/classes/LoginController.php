<?php
namespace App;
require_once APPSPATH.'/include/dwUDB.php';

class LoginController extends \Swoole\Controller
{
    function __construct(\Swoole $swoole)
    {
        parent::__construct($swoole);
        $swoole->session->start();
        if (empty($_SESSION['isLogin']))
        {
            $this->swoole->http->redirect($this->swoole->config['login']['login_url']);
            $this->swoole->http->finish();
        }
    }
}