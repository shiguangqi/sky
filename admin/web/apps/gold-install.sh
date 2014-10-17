#! /bin/sh

# master安装脚本
# @ shiguangqi
#
ROOT=/data/webroot/sky.duowan.com
# 安装admin项目下的web核心部分
NAME=web
DATE=$(date +%Y%m%d%H:%M:%S)

mv $ROOT/admin/$NAME $ROOT/admin/backup/$NAME.${DATE}.bak

if [ $? -eq 0 ];then
    mv $NAME $ROOT/admin/$NAME
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi
