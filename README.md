sky
===
##### 
发布+监控系统,master中心节点接受node节点上报数据,支持web后台和命令行控制,基于tcp协议的简单通信协议
#####
###### 启动流程
1. master服务 php master/run.php 监控node节点端口和控制(ctl)节点端口,默认分别为9999,9998
2. node服务  php node/run.php 启动一个节点,并在服务中创建客户端与maser链接,随之启动一个upload_server的服务,默认监控端口9507
3. websocket服务 php admin/server/run.php 启动websocket实时展示,控制node节点, master节点和node节点保持通信


#### 功能 

1. master服务中心和node节点tcp协议通信
2. 协议
    * node 节点控制协议
      1. getallnode
      2. getnode -n x
      3. getallgroup
      4. getgroup -g x
      5. setgroup -n x -g x
    * file 文件传输协议
      1. sendnode -n x -f /xxx/xx
      2. sendgroup -g x -f /xxx/xx
    * str 服务协议
    * sub 订阅协议
    * heart 心跳协议

#### 使用方式
