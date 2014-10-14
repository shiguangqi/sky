#! /bin/sh

# 项目安装脚本
# @ shiguangqi
#

DATE=$(date +%Y%m%d%H:%M:%S)
#第一次创建

if [ ! -d /var/www/master ]; then
    mkdir -p /var/www/master
else
    mv /var/www/master /var/www/master.${DATE}.bak
fi

if [ $? -eq 0 ];then
    mv master /var/www/master
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi
