
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
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-default">
<!--                    <div class="panel-heading">-->
<!--                        <h4 class="panel-title">-->
                            <a style="color: #fff;width: 100%;display: inline-block;text-align: left" class="btn btn-warning btn-sm" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                项目
                            </a>
<!--                        </h4>-->
<!--                    </div>-->
                    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel">
                        <div class="panel-body">
<!--                            <div class="well well-sm">-->
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
                                <input name="project" type="hidden" value="">
                                <div class="list-group file-list">
                                </div>
                                <input name="file" type="hidden" value="">
                                <h6><small>背景绿色为已安装版本,蓝色表示当前选择版本</small></h6>
<!--                            </div>-->
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
<!--                    <div class="panel-heading">-->
<!--                        <h4 class="panel-title">-->
                            <a style="color: #fff;width: 100%;display: inline-block;text-align: left" class="btn btn-success btn-sm" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                节点服务群组
                            </a>
<!--                        </h4>-->
<!--                    </div>-->
                    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel">
                        <div class="panel-body">
                            <table id="node-list" class="table table-bordered table-hover table-condensed">
                                <thead>
                                <tr class="info">
                                    <th style='width: 30px'></th>
                                    <th>选择</th>
                                    <th>节点</th>
                                    <th>地址</th>
                                    <th>端口</th>
                                    <!--                        <th>分组</th>-->
                                    <th>最后一次心跳时间</th>
                                </tr>
                                </thead>
                            </table>

                            <button type="button" class="btn btn-success btn-release" data-loading-text="安装中...">安装</button>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
<!--                    <div class="panel-heading">-->
<!--                        <h4 class="panel-title">-->
                            <a style="color: #fff;width: 100%;display: inline-block;text-align: left" class="btn btn-info btn-sm" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Console
                            </a>
<!--                        </h4>-->
<!--                    </div>-->
                    <div id="collapseThree" class="panel-collapse collapse" role="tabpanel">
                        <div class="panel-body">
                            <div class="console-box panel panel-default" style="background-color: #222;color: #fff;height: 150px;overflow-y: scroll;">
                                <div class="console-bg panel-body" >
                                    <div class="console">
                                    </div>
                                </div>
                            </div>
                        </div>
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
    $('.collapse').collapse({
        toggle: false
    });
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
        //$btn.button('reset');
        var checked = [];
        $('input:checkbox:checked').each(function() {
            checked.push($(this).val());
        });
        var filename = $("input[name='file']").val();
        var project = $("input[name='project']").val();
        if (project == '')
        {
            alert('请选择项目');
            return false;
        }
        else if (filename == '')
        {
            alert('请选择项目文件');
            return false;
        }
        else if (count(checked) == 0)
        {
            alert('请选择节点');
            return false;
        }
        else
        {
            var $btn = $(this).button('loading');
            msg = {};
            msg.service = 'file';
            msg.cmd = 'sendnodes';
            msg.n = checked;
            msg.f = filename;
            msg.p = project;
            ws.send($.toJSON(msg));
            return true;
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
                var string = '<div>成功获取以下节点</div>';
                var node = '';

                for (var i in text)
                {
                    node += addTr(text[i]);//"<tr id='"+text[i]['fd']+"' class='success'><td><input type='checkbox' value='"+text[i]['fd']+"'></td><td>"+text[i]['name']+"</td><td>"+text[i]['host']+"</td><td>"+text[i]['port']+"</td><td class='last_time'>"+date('Y-m-d H:i:s',text[i]['last_time'])+"</td></tr>";
                    string = string + "<div>host:"+text[i]['host']+" -- port:"+text[i]['port']+" -- group:"+text[i]['group']+"</div>";
                }
                term.echo(string);
                $("#node-list").append(node);
                break;
            case 'delnode':
                var text = res.data;
                $("#"+text.fd).css('color','#fff');
                $("#"+text.fd).removeClass("success");
                $("#"+text.fd).children('.last_time').css('background-color','#c12e2a');
                $("#"+text.fd).css('background-color','#c12e2a');
                $("."+text.fd+"_daemon").remove();
                break;
            case 'addname':
            case 'addnode':
                var text = res.data;
                var node = '';
                if ($("#"+text['fd']).html() != '')
                {
                    $("#"+text['fd']).remove();
                }
                var node = addTr(text);//"<tr id='"+text['fd']+"' class='success'><td><input type='checkbox' value='"+text['fd']+"'></td><td>"+text['name']+"</td><td>"+text['host']+"</td><td>"+text['port']+"</td><td class='last_time'>"+date('Y-m-d H:i:s',text['last_time'])+"</td></tr>";
                var string = '<div>Master新增节点</div>';
                string = string + "<div>host:"+text['host']+" -- port:"+text['port']+" -- group:"+text['group']+"</div>";
                term.echo(string);
                $("#node-list").append(node);
                break;
        }
    }

    function addTr(o)
    {
        var tr = '';
        var name = o.fd;
        if (o.name != undefined)
        {
            name = o.name;
        }
        tr = "<tr id='"+ o.fd+"' class='success'>" +
                "<td><span id='"+ o.fd+"_toggle' style='display: none' class='glyphicon glyphicon-play' onclick=toggleDaemon(this) ></span></td>" +
                "<td><span><input type='checkbox' value='"+ o.fd+"'></span></td>" +
                "<td>"+ name +"</td><td>"+o.host+"</td>" +
                "<td>"+ o.port+"</td>" +
                "<td class='last_time'>"+date('Y-m-d H:i:s', o.last_time)+"</td>" +
            "</tr>";
        return tr;
    }

    function heartHandler(res)
    {
        switch(res.cmd)
        {
            case 'bit' :
                var text = res.data;
                for (var i in text)
                {
                    $("#"+i).children('.last_time').empty().html(date('Y-m-d H:i:s',text[i].last_time));
                    $("#"+i+"_toggle").show();
//                    $("#"+i).children('.last_time').css('background-color','#419641');
//                    $("#"+i).children('.last_time').css('color','#fff');
                    var daemon = text[i].daemon;

                    if (daemon != undefined)
                    {

                        for (var j in daemon)
                        {
                            var running = '正在运行<span><button node='+i+' service='+daemon[j].name+' style="height: 18px;" type="button" ' +
                                'class="btn btn-danger btn-xs" onclick="stopService(this)">停止服务</button></span>';
                            var status_flag = '<span class="glyphicon glyphicon-ok"></span>';
                            if (daemon[j]._pid == 0)
                            {
                                status_flag = '<span class="glyphicon glyphicon-remove"></span>';
                                running = '停止运行<span><button node='+i+' service='+daemon[j].name+' style="height: 18px;" type="button" ' +
                                    'class="btn btn-success btn-xs" onclick="startService(this)">启动服务</button></span>';
                            }
                            var td = "<td colspan=6 style='padding: 0'>" +
                                        "<div class='row show-grid'>" +
                                            "<div class='col-sm-2'><span class='glyphicon glyphicon-user'></span>Name:"+daemon[j].name+"</div>" +
                                            "<div class='col-sm-2'><span class='glyphicon glyphicon-home'></span>Host:"+text[i].host+"</div>" +
                                            "<div class='col-sm-2'><span class='glyphicon glyphicon-record'></span>Port:"+daemon[j].port+"</div>" +
                                            "<div class='col-sm-2'><span class='glyphicon glyphicon-circle-arrow-right'></span>Pid:"+daemon[j]._pid+"</div>" +
                                            "<div class='col-sm-2'>"+status_flag+running+"</div>" +
                                            "<div class='col-sm-2'><span class='glyphicon glyphicon-time'></span>"+date('Y-m-d H:i:s',text[i].last_time)+"</div>" +
                                        "</div>" +//
                                     "</td>";
                            var tr = "<tr class='"+i+"_daemon' style='display:none' id='"+i+"_"+daemon[j].name+"'>" + td + "</tr>";
                            if ((daemon[j].name != undefined) && ($("#"+i+"_"+daemon[j].name).length == 0))
                            {
                                $("#"+i).after(tr);
                            }
                            else
                            {
                                $("#"+i+"_"+daemon[j].name).html(td);
                            }
                        }
                    }
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

    function toggleDaemon(o)
    {
        var id = $(o).parent().parent().attr('id');
        $("."+id+"_daemon").toggle();
    }

    function startService(o)
    {
        $(o).remove();
        var node = $(o).attr("node");
        var service = $(o).attr("service");
        msg = {};
        msg.service = 'cmd';
        msg.cmd = 'start';
        msg.sn = node;
        msg.s = service;
        ws.send($.toJSON(msg));
        return true;
    }

    function stopService(o)
    {
        $(o).remove();
        var node = $(o).attr("node");
        var service = $(o).attr("service");
        msg = {};
        msg.service = 'cmd';
        msg.cmd = 'stop';
        msg.sn = node;
        msg.s = service;
        ws.send($.toJSON(msg));
        return true;
    }

</script>
</body>
</html>
