<?php
require_once APPSPATH.'/include/dwUDB.php';

class page extends Swoole\Controller
{
    function index()
    {
        //https
        if ($_SERVER['SERVER_PORT'] != 80)
        {
            $this->swoole->http->redirect($this->swoole->config['login']['login_url']);
        }
        $this->swoole->session->start();
        if (!empty($_SESSION['isLogin']))
        {
            $this->swoole->http->redirect($this->swoole->config['login']['home_url']);
        }
        else
        {
            $this->display();
        }
    }

    function logout()
    {
        $this->swoole->session->start();
        Swoole\Cookie::delete('username');
        Swoole\Cookie::delete('password');
        Swoole\Cookie::delete('osinfo');
        Swoole\Cookie::delete('oauthCookie');
        unset($_SESSION['userinfo'], $_SESSION['isLogin']);
        $this->swoole->http->redirect($this->swoole->config['login']['login_url']);
    }

    function login()
    {
        $this->swoole->session->start();
        if (!empty($_SESSION['isLogin']))
        {
            home:
            $this->swoole->http->redirect($this->swoole->config['login']['home_url']);
        }
        else
        {
            $login = new \dwUDB;
            $result = $login->isLogin();
            if (empty($result))
            {
                $this->swoole->http->redirect($this->swoole->config['login']['login_url']);
            }
            else
            {
                $this->collect_user();
                $_SESSION['userinfo'] = $result;
                $_SESSION['isLogin'] = true;
                goto home;
            }
        }
    }

    function collect_user()
    {
        $uid = $_COOKIE['yyuid'];
        if (!table('user')->exists(array('uid' => $uid)))
        {
            $puts['uid'] = $uid;
            $puts['username'] = $_COOKIE['username'];
            table('user')->put($puts);
        }
    }
}