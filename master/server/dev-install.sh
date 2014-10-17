#! /bin/sh

# master安装脚本
# @ shiguangqi
#
ROOT=/var/www
# 安装master项目下的server核心部分
NAME=server
DATE=$(date +%Y%m%d%H:%M:%S)

mv $ROOT/master/$NAME $ROOT/master/backup/$NAME.${DATE}.bak

if [ $? -eq 0 ];then
    mv $NAME $ROOT/master/$NAME
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi
