<?php
namespace App\Controller;
use Swoole;

class File extends \App\LoginController
{

    public function upload()
    {
        $projects = table('project')->gets(array('order'=>'id asc'));
        $this->assign('projects', $projects);
        $this->display();
    }

    public function upload_action()
    {
        $project = $_POST['project'];
        $this->path = "/static/upload";
        $upload = new Swoole\Upload($this->path);
        $upload->allow = array('tar','gzip','bin');
        $upload->shard_type = 'user';
        $upload->shard_argv = $project;

        if (!empty($_FILES))
        {
            $filename = $_FILES['filename']['name'];
            $res = $upload->save('filename',$filename);
            if ($res)
                Swoole\JS::js_goto('上传成功','/file/upload');
            else
                var_dump($upload->error_msg());
        }
    }
}