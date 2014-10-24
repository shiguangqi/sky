sky
===
##### 集成监控,发布,重启控制等功能

###### 服务
1. master中心节点服务
   * 监控节点,转发控制指令和节点信息
2. node 节点服务
   * 按照配置收集,上报节点信息,处理下发指令,安装,启动,停止,重启等指令.
3. websocket 控制节点服务
   * 转发web命令,上报,接受,回写数据.
4. container 容器服务
   
###### 目录约定
   * backup 自动备份目录
   * configs 配置文件
   * init.d 启动控制目录
   * log 日志目录
   * run pid目录

#### 使用方式

1. ./master/init.d/sky_master start [stop restart]
2. ./node/init.d/sky_node start [stop restart]
3. ./websocket/init.d/sky_ws start [stop restart]

###### node 配置动监控服务
/node/configs/node.ini
```
[monitor]
   mostats_svr[name] = server_name
   mostats_svr[pid] = path/to/server_name.pid
   mostats_svr[host] = host
   mostats_svr[port] = port
   mostats_svr[init] = path/to/server/init.d/server_name
```
#####后台效果

