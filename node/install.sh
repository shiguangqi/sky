#!/bin/sh
DATE=$(date +%Y%m%d%H:%M:%S)
cd /tmp/
tar zxf $1
#第一次创建
if [ ! -d /var/www/node ]; then
    echo 'create first time';
    mkdir -p /var/www/node
else
    mv /var/www/node /var/www/node.${DATE}.bak
fi

if [ $? -eq 0 ];then
    echo 'update project first time';
    mv node /var/www/node
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi