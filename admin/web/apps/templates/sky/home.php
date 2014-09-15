
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>SKY</title>
    <link href="/static/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/apps/static/css/style.css" rel="stylesheet">
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">SKY监控系统</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <?php require __DIR__.'/../include/leftmenu.php';?>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div class="page-header">
                <h3><small>项目</small></h3>
            </div>
            <div class="well well-sm">
                <ul class="nav nav-pills" role="tablist">
                    <?php
                        if (!empty($projects))
                        {
                            foreach ($projects as $f)
                            {
                    ?>
                        <li role="presentation" class="project" name="<?php echo $f['project_name'];?>"><a href="#"><?php echo $f['nick_name'];?></a></li>
                    <?php
                            }
                        }
                    ?>
                </ul>
                <ul class="list-group file-list">
                </ul>
            </div>
            <div class="page-header">
                <h3><small>服务群组</small></h3>
            </div>
            <table id="node-list" class="table table-bordered">
                <thead>
                    <tr class="info">
                        <th>选择</th>
                        <th>节点</th>
                        <th>地址</th>
                        <th>端口</th>
<!--                        <th>分组</th>-->
                        <th>最后一次心跳时间</th>
                    </tr>
                </thead>
            </table>

            <button type="button" class="btn btn-success btn-release">安装</button>


            <div class="page-header">
                <h4><small>Console</small></h4>
            </div>
            <div class="console-box panel panel-default" style="background-color: #222;color: #fff;height: 150px;overflow-y: scroll;">
                <div class="console-bg panel-body" >
                    <div class="console">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/static/vendor/jquery/jquery-2.0.2.min.js"></script>
<script src="/static/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="/static/vendor/jquery/jquery.json.js"></script>
<script src="/apps/static/js/php.js"></script>
<script src="/apps/static/js/sky.js"></script>
<script>
    var config = <?php echo json_encode($config);?>;
    var term = {};
    term.ps1_flag = "<span class='ps1'> ></span>";
    term.welcome = "<span>欢迎SKY</span>";
    term.echo = function($msg) {
        var line = "<span>"+$msg+"</span>";
        $(".console").append(line);
        term.resize();
    };

    term.clear = function() {
        $(".console").empty();
    }

    term.ps1 = function() {
        $(".console-bg").append(term.ps1_flag);
    }

    term.resize = function () {
        var _term = $(".console-box").innerHeight();
        var _log = $(".console-bg").innerHeight();
        if (_term < _log) {
            $(".console-box").scrollTop(_log - _term + 20);
        }
    }
    $(document).ready(function () {
        term.echo(term.welcome);
        term.ps1();
        connect();
    });

    $(".btn-release").click(function(){
        var checked = [];
        $('input:checkbox:checked').each(function() {
            checked.push($(this).val());
        });
        var filename = $("input[name='file']:checked").val();
        var project = '';
        $(".project").each(function(){
            if ($(this).hasClass("active"))
            {
                project = $(this).attr('name');
            }
        });

        if (filename == undefined)
        {
            alert('请选择项目文件');
            return;
        }
        if (count(checked) == 0)
        {
            alert('请选择节点');
            return;
        }
        else
        {
            msg = {};
            msg.service = 'file';
            msg.cmd = 'sendnodes';
            msg.n = checked;
            msg.f = filename;
            msg.p = project;
            ws.send($.toJSON(msg));
        }
    });

    function connect()
    {
        if (window.WebSocket || window.MozWebSocket) {
            ws = new WebSocket(config.server);
            listen();
        }
    }

    function exit()
    {
        term.echo("服务已关闭");
    }
    var self = $(".console");

    function listen()
    {
        ws.onopen = function (e) {
            msg = {};
            msg.service = 'node';
            msg.cmd = 'webinit';
            ws.send($.toJSON(msg));
        };
        ws.onmessage = function (e) {
            var res = $.evalJSON(e.data);
            onReceive(res);
        };
        /**
         * 连接关闭事件
         */
        ws.onclose = function (e) {
            term.echo("关闭服务器成功");
            running = 2;
        };
        /**
         * 异常事件
         */
        ws.onerror = function (e) {
            term.echo("服务器发生异常");
            running = 3;
        };
    }

    function onReceive(res)
    {
        switch(res.service)
        {
            case 'node':
                nodeHandler(res);
                break;
            case 'file':
                fileHandler(res);
                break;
            case 'heart':
                heartHandler(res);
                break;
        }
    }

    function nodeHandler(res)
    {
        switch(res.cmd)
        {
            case 'webinit':
                var text = res.data.node;
                var console = '<div>成功获取以下节点</div>';
                var node = '';

                for (var i in text)
                {
                    node = node + "<tr id='"+text[i]['fd']+"' class='success'><td><input type='checkbox' value='"+text[i]['fd']+"'></td><td>"+text[i]['fd']+"</td><td>"+text[i]['host']+"</td><td>"+text[i]['port']+"</td><td class='last_time'>"+date('Y-m-d H:i:s',text[i]['last_time'])+"</td></tr>";
                    console = console + "<div>host:"+text[i]['host']+" -- port:"+text[i]['port']+" -- group:"+text[i]['group']+"</div>";
                }
                term.echo(console);
                $("#node-list").append(node);
                break;
            case 'delnode':
                var text = res.data;
                $("#"+text.fd).css('color','#fff');
                $("#"+text.fd).removeClass("success");
                $("#"+text.fd).children('.last_time').css('background-color','#c12e2a');
                $("#"+text.fd).css('background-color','#c12e2a');
                break;
            case 'addnode':
                var text = res.data;
                var node = '';
                if ($("#"+text['fd']).html() != '')
                {
                    $("#"+text['fd']).remove();
                }
                var node = "<tr id='"+text['fd']+"' class='success'><td><input type='checkbox' value='"+text['fd']+"'></td><td>"+text['fd']+"</td><td>"+text['host']+"</td><td>"+text['port']+"</td><td class='last_time'>"+date('Y-m-d H:i:s',text['last_time'])+"</td></tr>";
                var console = '<div>Master新增节点</div>';
                console = console + "<div>host:"+text['host']+" -- port:"+text['port']+" -- group:"+text['group']+"</div>";
                term.echo(console);
                $("#node-list").append(node);
                break;
        }
    }

    function heartHandler(res)
    {
        //console.info(res);
        switch(res.cmd)
        {
            case 'send' :
                var text = res.data;
                for (var i in text)
                {
                    $("#"+i).children('.last_time').empty().html(date('Y-m-d H:i:s',text[i].last_time));
                    $("#"+i).children('.last_time').css('background-color','#419641');
                    $("#"+i).children('.last_time').css('color','#fff');
                }

        }
    }
    function fileHandler(res)
    {
        switch(res.cmd)
        {
            case 'sendnodes':
                var console = '<div>文件发布</div>';
                term.echo(res.data.msg+"</br>");
                break;
        }

    }

</script>
</body>
</html>
