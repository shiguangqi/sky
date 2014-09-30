<?php
namespace App\Controller;
use Swoole;

class Sky extends \App\LoginController
{
    public function home()
    {

        $config = $this->config['websocket'];
        $projects = table('project')->gets(array('order'=>'id asc'));
        $this->assign('projects', $projects);
        $this->assign('config', $config);
        $this->assign('user', $_SESSION['userinfo']);
        $this->display();
    }

    public function getProjectFiles()
    {
        $return = array();
        if (empty($_POST))
        {
            $return['status'] = 400;
        }
        else
        {
            $project = $_POST['name'];
            $info = table('project')->get($project,'project_name')->get();
            $params['project_name'] =  $project;
            $release = table('version')->gets($params);
            $content = array();
            foreach ($release as $k => $filename)
            {
                if (!file_exists(WEBPATH.$filename['path']))
                {
                    continue;
                }
                $content[$k]['filename'] = basename($filename['path']);
                $content[$k]['release'] = $filename['version'];
            }
            $return['status'] = 200;
            $return['content'] = $content;
            $return['current_release'] = $info['current_release'];
        }
        echo json_encode($return);
    }

    public function getRelease($name)
    {
        $tmp = explode('_',$name);
        return $tmp[1];
    }
}