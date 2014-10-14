#! /bin/sh

# 项目安装脚本
# @ shiguangqi
#

DATE=$(date +%Y%m%d%H:%M:%S)
#第一次创建

if [ ! -d /var/www/node ]; then
    mkdir -p /var/www/node
else
    mv /var/www/node /var/www/node.${DATE}.bak
fi

if [ $? -eq 0 ];then
    mv node /var/www/node
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi
