    var config = {
        'server' : 'ws://127.0.0.1:9909'
    };
    var GET = getRequest();
    var LogConfig = {};
    $(document).ready(function () {
        if (window.WebSocket || window.MozWebSocket) {
                ws = new WebSocket(config.server);
                //listenEvent();
            } else {
                WEB_SOCKET_SWF_LOCATION = "/static/flash-websocket/WebSocketMain.swf";
                $.getScript("/static/flash-websocket/swfobject.js", function(){
                    $.getScript("/static/flash-websocket/web_socket.js", function(){
                        ws = new WebSocket(config.server);
                        listenEvent();
                    });
                });
            }
    });

    function listenEvent()
    {
        ws.onopen = function (e) {
            //模块id 和接口id 合法才可以连接服务器
            LogConfig.module_id = GET['module_id'];
            LogConfig.interface_id = GET['interface_id'];
            if (LogConfig.module_id == undefined || LogConfig.interface_id == undefined) {
                alert('必须指定模块和接口～');
                ws.close();
                return false;
            }
            //todo 用户验证
            //msg = new Object();
            //msg.cmd = 'tailf';
            //msg.id = GET['name'];
            //ws.send($.toJSON(msg));
        };

        //有消息到来时触发
        ws.onmessage = function (e) {
            var log = $.evalJSON(e.data);
            var cmd = log.cmd;
            if (cmd == 'show')
            {
                showLog(message);
            }
            else if (cmd == 'getOnline')
            {
                showOnlineList(message);
            }
            else if (cmd == 'getHistory')
            {
                showHistory(message);
            }
            else if (cmd == 'newUser')
            {
                showNewUser(message);
            }
            else if (cmd == 'fromMsg')
            {
                showNewMsg(message);
            }
            else if (cmd == 'offline')
            {
                var cid = message.fd;
                delUser(cid);
                showNewMsg(message);
            }
        };

        /**
         * 连接关闭事件
         */
        ws.onclose = function (e) {
            if (confirm("聊天服务器已关闭")) {
                //alert('您已退出聊天室');
                location.href = 'index.html';
            }
        };

        /**
         * 异常事件
         */
        ws.onerror = function (e) {
            alert("异常:" + e.data);
            console.log("onerror");
        };
    }

    function getRequest() {
        var url = location.search; // 获取url中"?"符后的字串
        var theRequest = new Object();
        if (url.indexOf("?") != -1) {
            var str = url.substr(1);

            strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                var decodeParam = decodeURIComponent(strs[i]);
                var param = decodeParam.split("=");
                theRequest[param[0]] = param[1];
            }

        }
        return theRequest;
    }