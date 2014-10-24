<?php
namespace Container\Protocol;
use Swoole;

require_once LIBPATH . '/function/cli.php';
class AppServer extends \Container\Protocol\HttpServer
{
    protected $router_function;
    protected $apps_path;

    function onStart($serv)
    {
        parent::onStart($serv);
        //todo load project 下所有的公共文件
        $_project = ucwords($this->config['name']);
        Swoole\Loader::setRootNS($_project, $this->document_root.'/'.'classes');
        Swoole::$project = $_project;
        //增加项目根目录下的app/classes命名空间 自动载入
        $app_paths = scandir($this->document_root);
        foreach ($app_paths as $app)
        {
            if (!in_array($app,array(".","..")))
            {
                Swoole\Loader::setRootNS(ucwords($app), $this->document_root.'/'.$app.'/classes');
            }
        }
        Swoole::getInstance()->addHook(Swoole::HOOK_CLEAN, function(){
            $php = Swoole::getInstance();
            //模板初始化
            if (!empty($php->tpl))
            {
                $php->tpl->clear_all_assign();
            }
            //还原session
            if (!empty($php->session))
            {
                $php->session->open = false;
                $php->session->readonly = false;
            }
        });
    }

    function onRequest(Swoole\Request $request)
    {
        $response = new Swoole\Response();
        $request->setGlobal();
        $tmp = explode('/',ltrim($request->meta['path'],'/'));
        Swoole::$app_path = $this->document_root.'/'.$tmp[0];
        Swoole::$app = ucwords($tmp[0]);
        //处理静态请求
        if (!empty($this->config['apps']['do_static']) and $this->doStaticRequest($request, $response))
        {
            return $response;
        }

        $php = Swoole::getInstance();
        //将对象赋值到控制器
        $php->request = $request;
        $php->response = $response;

        try
        {
            ob_start();
            /*---------------------处理MVC----------------------*/
            $response->body = $php->runApp();
            $response->body .= ob_get_contents();
            ob_end_clean();
        }
        catch(\Exception $e)
        {
            if ($request->finish != 1) $this->httpError(404, $response, $e->getMessage());
        }
        //重定向
        if (isset($response->head['Location']))
        {
            $response->send_http_status(301);
        }
        return $response;
    }
}