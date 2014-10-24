sky
===
##### 集成监控,发布,重启控制等功能

###### 服务
1. master中心节点服务
2. node 节点服务
3. websocket 控制节点服务
4. container 容器服务

###### 功能
1. master服务中心
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
