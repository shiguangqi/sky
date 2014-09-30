<?php
namespace App\Controller;
use Swoole;

class File extends \App\LoginController
{

    public function upload()
    {
        $projects = table('project')->gets(array('order'=>'id asc'));
        $this->assign('projects', $projects);
        $this->assign('username', $_SESSION['userinfo']['username']);
        $this->display();
    }

    public function upload_action()
    {
        $project = $_POST['project_name'];
        $this->path = "/static/upload";
        $upload = new Swoole\Upload($this->path);
        $upload->allow = array('tar','gzip','bin');
        $upload->shard_type = 'user';
        $upload->shard_argv = $project;

        if (!empty($_FILES))
        {
            if (!empty($_POST['version']) and !empty($_POST['project_id']))
            {
                $filename = $_FILES['filename']['name'];
                $filename = $filename."__".$_POST['version'];

                $version['project_id'] = $_POST['project_id'];
                $version['project_name'] = $_POST['project_name'];
                $version['create_by'] = $_POST['create_by'];
                $version['version'] = $_POST['version'];
                $version['path'] = $this->path.'/'.$project.'/'.$filename;
                $insert_id = table('version')->put($version);

                $res = $upload->save('filename',$filename);
                if ($res)
                    Swoole\JS::js_goto('上传成功','/file/upload');
                else
                    var_dump($upload->error_msg());
            }
        }
    }
}