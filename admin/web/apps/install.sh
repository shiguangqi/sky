#! /bin/sh

# master安装脚本
# @ shiguangqi
#
ROOT=/data/webroot/sky.duowan.com
# 安装admin/web项目下的apps核心部分
NAME=apps
DATE=$(date +%Y%m%d%H:%M:%S)

mv $ROOT/admin/web/$NAME $ROOT/admin/backup/$NAME.${DATE}.bak

if [ $? -eq 0 ];then
    mv $NAME $ROOT/admin/web/$NAME
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi
