<ul class="nav nav-sidebar">
    <li <?php if(Swoole::$php->env['mvc']['controller'] == 'sky') echo 'class="active"';?>><a href="/sky/home">监控</a></li>
    <li <?php if(Swoole::$php->env['mvc']['controller'] == 'file') echo 'class="active"';?>><a href="/file/upload">文件上传</a></li>
    <li <?php if(Swoole::$php->env['mvc']['controller'] == 'project') echo 'class="active"';?>><a href="/project/add">新增项目</a></li>
</ul>