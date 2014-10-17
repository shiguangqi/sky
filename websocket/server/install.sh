#! /bin/sh

# master安装脚本
# @ shiguangqi
#
ROOT=/data/webroot/sky.duowan.com
# 安装websocket项目下的server核心部分
NAME=server
DATE=$(date +%Y%m%d%H:%M:%S)

mv $ROOT/websocket/$NAME $ROOT/websocket/backup/$NAME.${DATE}.bak

if [ $? -eq 0 ];then
    mv $NAME $ROOT/websocket/$NAME
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi
