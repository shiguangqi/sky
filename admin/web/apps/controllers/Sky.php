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
        $this->display();
    }

    public function getProjectFiles()
    {
        $this->path =  WEBPATH.'/static/upload';
        $return = array();
        if (empty($_POST))
        {
            $return['status'] = 400;
        }
        else
        {
            $project = $_POST['name'];
            $info = table('project')->get($project,'project_name')->get();
            $dirs = scandir($this->path.'/'.$project);
            $content = array();
            foreach ($dirs as $k => $filename)
            {
                if ($filename == '.' || $filename == '..')
                {
                    continue;
                }
                $content[$k]['filename'] = $filename;
                $content[$k]['release'] = $this->getRelease($filename);
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