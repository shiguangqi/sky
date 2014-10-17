#! /bin/sh

# node安装脚本
# @ shiguangqi
#
ROOT=/var/www
# 安装node项目下的server核心部分
NAME=server
DATE=$(date +%Y%m%d%H:%M:%S)

mv $ROOT/node/$NAME $ROOT/node/backup/$NAME.${DATE}.bak

if [ $? -eq 0 ];then
    mv $NAME $ROOT/node/$NAME
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi
