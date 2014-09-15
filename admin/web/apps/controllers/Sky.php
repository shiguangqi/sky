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

    public function getFiles()
    {
        $this->path =  WEBPATH.'/static/upload';
        $params['order'] = 'id asc';
        $params['id'] = 1;
        $projects = table('project')->get(1)->get();

        $dirs = scandir($this->path.'/'.$projects['project_name']);
        $filenames = array();
        foreach ($dirs as $filename)
        {
            if ($filename == '.' || $filename == '..')
            {
                continue;
            }
            $filenames[] = $filename;
        }
        return $filenames;
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
            $dirs = scandir($this->path.'/'.$project);
            foreach ($dirs as $filename)
            {
                if ($filename == '.' || $filename == '..')
                {
                    continue;
                }
                $content[] = $filename;
            }
            $return['status'] = 200;
            $return['content'] = $content;
        }
        echo json_encode($return);
    }
}