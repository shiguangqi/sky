$('.collapse').collapse({
    toggle: false
});
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

var version = '';
var project = '';
var filename = '';
$("#install").click(function(){
    var checked = [];
    $('input:checkbox:checked').each(function() {
        checked.push($(this).val());
    });
    filename = $("input[name='file']").val();
    project = $("input[name='project']").val();
    version = $("input[name='version']").val();
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
        //$btn.button('reset');
        msg = {};
        msg.service = 'file';
        msg.cmd = 'sendnodes';
        msg.n = checked;
        msg.f = filename;
        msg.v = version;
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
        case 'cmd':
            cmdHandler(res);
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

            for (var i in text)
            {
                if ($(document.getElementById(text[i]['host'])).html() == text[i]['host'])
                {
                    $(document.getElementById(text[i]['host'])).parent().remove();
                }
                $("#node-list").append(addTr(text[i]));
                getNodeInfo(text[i]);
                string = string + "<div>host:"+text[i]['host']+" -- port:"+text[i]['port']+"</div>";
            }
            term.echo(string);
            break;
        case 'delnode':
            var text = res.data;
            $("#"+text.fd).removeClass("success");
            $("#"+text.fd).addClass("danger");
            $("#"+text.fd).children().children().empty();
            $("#"+text.fd+"_toggle").remove();
            $("."+text.fd+"_daemon").remove();
            break;
        case 'addname':
        case 'addnode':
            var text = res.data;
            if ($("#"+text['fd']).html() != '')
            {
                $("#"+text['fd']).remove();
            }
            if ($(document.getElementById(text['host'])).html() == text['host'])
            {
                $(document.getElementById(text['host'])).parent().remove();
            }
            var string = '<div>Master新增节点</div>';
            string = string + "<div>host:"+text['host']+" -- port:"+text['port']+"</div>";
            term.echo(string);
            $("#node-list").append(addTr(text));
            getNodeInfo(text);
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
            "<td><span id='"+ o.fd+"_toggle' class='glyphicon glyphicon-play' onclick=toggleDaemon(this) ></span></td>" +
            "<td><span><input type='checkbox' value='"+ o.fd+"'></span></td>" +
            "<td>"+ name +"</td><td>"+o.host+"</td>" +
            "<td class='last_time'>"+date('Y-m-d H:i:s', o.last_time)+"</td>" +
        "</tr>";
    return tr;
}

function getNodeInfo(o)
{
    var ip = o.host;
    var fd = o.fd;
    //从数据库获取信息
    $.ajax({
        url: '/sky/getNodeInfo',
        dataType : 'json',
        data: {'ip':ip},
        method: 'post',
        success: function(data) {
            if (data.status == 200)
            {
                var td = "<th colspan=5 style='padding: 0'>" +
                        "<div class='daemon-row'>" +
                            "<div class='col-sm-1'><span style='padding: 6px 0 0 30px'>Name</span></div>" +
                            "<div class='col-sm-1'><span style='padding: 6px 0'>Version</span></div>" +
                            "<div class='col-sm-1'><span style='padding: 6px 0'>Type</span></div>" +
                            "<div class='col-sm-2'><span style='padding: 6px 0'>Last install time</span></div>" +
                            "<div class='col-sm-1'><span style='padding: 6px 0'>Install by</span></div>" +
                            "<div class='col-sm-2'><span style='padding: 6px 0'>Start time</span></div>" +
                            "<div class='col-sm-1'><span style='padding: 6px 0'>Start by</span></div>" +
                            "<div class='col-sm-1'><span style='padding: 6px 0'>Status</span></div>" +
                            "<div class='col-sm-2'><span style='padding: 6px 0'>Action</span></div>" +
                        "</div>"
                    "</th>";
                var tr = "<tr class='"+fd+"_daemon_head' style='display:none'>" + td + "</tr>";
                $("#"+ o.fd).after(tr);
                var content = data.content;
                for (var j in content)
                {
                    if (content[j]['type'] == 1)
                    {
                        var td = "<td colspan=5 style='padding: 0'>" +
                            "<div class='daemon-row'>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_name' style='padding: 6px 0'>"+content[j].name+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_version' style='padding: 6px 0'>"+content[j].version+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_type_name' style='padding: 6px 0'>"+content[j].type_name+"</span></div>" +
                            "<div class='col-sm-2'><span id='"+fd+"_"+content[j].name+"_last_start_time' style='padding: 6px 0'>"+content[j].last_install_time+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_last_install_name' style='padding: 6px 0'>"+content[j].last_install_name+"</span></div>" +
                            "<div class='col-sm-2'><span id='"+fd+"_"+content[j].name+"_last_start_time' style='padding: 6px 0'>"+content[j].last_start_time+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_last_start_name' style='padding: 6px 0'>"+content[j].last_start_name+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_status' style='padding: 6px 0'></span></div>" +
                            "<div class='col-sm-2'><span id='"+fd+"_"+content[j].name+"_action' style='padding: 6px 0'></span></div>" +
                            "</div>" +
                            "</td>";
                        var tr = "<tr class='"+fd+"_daemon' style='display:none' id='"+fd+"_"+content[j].name+"'>" + td + "</tr>";
                    }
                    else
                    {
                        var td = "<td colspan=5 style='padding: 0'>" +
                            "<div class='daemon-row'>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_name' style='padding: 6px 0'>"+content[j].name+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_version' style='padding: 6px 0'>"+content[j].version+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_type_name' style='padding: 6px 0'>"+content[j].type_name+"</span></div>" +
                            "<div class='col-sm-2'><span id='"+fd+"_"+content[j].name+"_last_install_time' style='padding: 6px 0'>"+content[j].last_install_time+"</span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_last_install_name' style='padding: 6px 0'>"+content[j].last_install_name+"</span></div>" +
                            "<div class='col-sm-2'><span id='"+fd+"_"+content[j].name+"_last_start_time' style='padding: 6px 0'></span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_last_start_name' style='padding: 6px 0'></span></div>" +
                            "<div class='col-sm-1'><span id='"+fd+"_"+content[j].name+"_status' style='padding: 6px 0'></span></div>" +
                            "<div class='col-sm-2'><span id='"+fd+"_"+content[j].name+"_action' style='padding: 6px 0'></span></div>" +
                            "</div>" +//
                            "</td>";
                        var tr = "<tr class='"+fd+"_daemon' style='display:none' id='"+fd+"_"+content[j].name+"'>" + td + "</tr>";
                    }

                    $("."+ o.fd +"_daemon_head").after(tr);
                }

            }
        }
    });
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
                var daemon = text[i].server.daemon;
                var monitor = text[i].server.monitor;
                var jsString = '';
                var run = '';
                if (daemon != undefined)
                {
                    showService(daemon,i,'Service');
                }

                if (monitor != undefined)
                {
                    showService(monitor,i,'Monitor');
                }
            }
    }
}

function showService(run,i,service)
{
    for (var j in run)
    {
        var running = '';
        var action = '';
        var action1 = '<span><button node='+i+' service='+run[j].name+'  type="button" ' +
            'class="btn btn-danger btn-xs" onclick="stop'+service+'(this)">停止服务</button></span>';
        var action2 = '<span><button node='+i+' service='+run[j].name+'  type="button" ' +
            'class="btn btn-success btn-xs" onclick="start'+service+'(this)">启动服务</button></span>';
        var action3 = '<span style="padding-left: 5px"><button node='+i+' service='+run[j].name+'  type="button" ' +
            'class="btn btn-info btn-xs" onclick="reStart'+service+'(this)">重启服务</button></span>';
        var action = action1+action3;
        if (run[j]._pid == 0)
        {
            running = '<span style="color: red" style="padding: 6px 0" class="glyphicon glyphicon-remove">停止运行</span>';
            action = action2;
        }
        else
        {
            running = '<span style="color: green" style="padding: 6px 0" class="glyphicon glyphicon-ok">正在运行</span>';
        }
        var td = "<td colspan=5 style='padding: 0'>" +
            "<div class='daemon-row'>" +
            "<div class='col-sm-1'><span id='"+i+"_"+run[j].name+"_name' style='padding: 6px 0'>"+run[j].name+"</span></div>" +
            "<div class='col-sm-1'><span id='"+i+"_"+run[j].name+"_version' style='padding: 6px 0'>"+run[j].version+"</span></div>" +
            "<div class='col-sm-1'><span id='"+i+"_"+run[j].name+"_type_name' style='padding: 6px 0'>"+run[j].type_name+"</span></div>" +
            "<div class='col-sm-2'><span id='"+i+"_"+run[j].name+"_last_start_time' style='padding: 6px 0'></span></div>" +
            "<div class='col-sm-1'><span id='"+i+"_"+run[j].name+"_last_install_name' style='padding: 6px 0'></span></div>" +
            "<div class='col-sm-2'><span id='"+i+"_"+run[j].name+"_last_start_time' style='padding: 6px 0'></span></div>" +
            "<div class='col-sm-1'><span id='"+i+"_"+run[j].name+"_last_start_name' style='padding: 6px 0'></span></div>" +
            "<div class='col-sm-1'><span id='"+i+"_"+run[j].name+"_status' style='padding: 6px 0'>"+running+"</span></div>" +
            "<div class='col-sm-2'><span id='"+i+"_"+run[j].name+"_action' style='padding: 6px 0'>"+action+"</span></div>" +
            "</div>" +
            "</td>";
        var tr = "<tr class='"+i+"_daemon' style='display:none' id='"+i+"_"+run[j].name+"'>" + td + "</tr>";
        if ((run[j].name != undefined) && ($("#"+i+"_"+run[j].name).length == 0))
        {
            $("#"+i+"_"+run[j].name+"_head").after(tr);
        }
        else
        {
            $("#"+i+"_"+run[j].name+"_status").html(running);
            $("#"+i+"_"+run[j].name+"_action").html(action);
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

function cmdHandler(res)
{
    var content = res.data.params;
    var node = res.data.params.n;
    switch(res.cmd)
    {
        case '_file_install':
            var status = content.s;
            $("#install").button('reset');
            if (status == 0)
            {
                //更新app版本
                $.ajax({
                    url: '/sky/updateVersion',
                    dataType : 'json',
                    data: {'name':project,'version':version},
                    method: 'post',
                    success: function(data) {
                        if (data.status == 200)
                        {
                            $("#"+node+"_"+project+"_version").html("<span style='color:red;font-weight: bold'>"+version+"</span>");
                        }
                    }
                });
            }
            term.echo(content.o+"</br>");
            break;
        case '_start_monitor':
            var status = content.s;
            term.echo(content.o+"</br>");
            break;
        case '_stop_monitor':
            var status = content.s;
            term.echo(content.o+"</br>");
            break;
    }
}

function toggleDaemon(o)
{
    var id = $(o).parent().parent().attr('id');
    $("."+id+"_daemon_head").toggle();
    $("."+id+"_daemon").toggle();
}

function reStartNode(o)
{
    var node = $(o).attr("node");
    msg = {};
    msg.service = 'cmd';
    msg.cmd = 'restart_node';
    msg.sn = node;
    msg.s = service;
    ws.send($.toJSON(msg));
}

function startService(o)
{
    $(o).remove();
    var node = $(o).attr("node");
    var service = $(o).attr("service");
    msg = {};
    msg.service = 'cmd';
    msg.cmd = 'start_service';
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
    msg.cmd = 'stop_service';
    msg.sn = node;
    msg.s = service;
    ws.send($.toJSON(msg));
    return true;
}

function startMonitor(o)
{
    $(o).remove();
    var node = $(o).attr("node");
    var name = $(o).attr("service");
    msg = {};
    msg.service = 'cmd';
    msg.cmd = 'start_monitor';
    msg.sn = node;
    msg.m = name;
    ws.send($.toJSON(msg));
    return true;
}

function stopMonitor(o)
{
    $(o).remove();
    var node = $(o).attr("node");
    var name = $(o).attr("service");
    msg = {};
    msg.service = 'cmd';
    msg.cmd = 'stop_monitor';
    msg.sn = node;
    msg.m = name;
    ws.send($.toJSON(msg));
    return true;
}

function reStartMonitor(o)
{
    $(o).remove();
    var node = $(o).attr("node");
    var name = $(o).attr("service");
    msg = {};
    msg.service = 'cmd';
    msg.cmd = 'restart_monitor';
    msg.sn = node;
    msg.m = name;
    ws.send($.toJSON(msg));
    return true;
}

//---------------------------------


initFiles();
$(".project").click(function(){
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    var project = $(this).attr('name');
    $(this).parent().prev().prev().html(project);
    $("input[name='project']").val(project);
    getProjectFiles(project);
    $("input[name='file']").val('');
    $("input[name='version']").val('');
    $("#choose-file").html('选择Files');
});

function initFiles()
{
    var project = $(".project").first().addClass('active').attr('name');
    $("input[name='project']").val(project);
    getProjectFiles(project);
}

function getFilename(o)
{
    var filename = $(o).html();
    $("#choose-file").html(filename);
    $(o).parent().addClass('active');
    $(o).parent().siblings().removeClass('active');
    $("input[name='file']").val(filename);
    $("input[name='version']").val($(o).attr('version'));
}

function getProjectFiles(project)
{
    $("#file-list").empty();
    $.ajax({
        url: '/sky/getProjectFiles',
        dataType : 'json',
        data: {'name':project},
        method: 'post',
        success: function(data) {
            if (data.status == 200)
            {
                var content = data.content;
                var current_release = data.current_release;
                var line = '';
                for (var i in content)
                {
                    line += '<li><a version='+content[i]['version']+' onclick="getFilename(this)">';
                    line += content[i].filename;
                    line += '</a></li>';
                }
                $("#file-list").append(line);
            }
        }
    });
}

