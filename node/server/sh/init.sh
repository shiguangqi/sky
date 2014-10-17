#!/bin/sh
DATE=$(date +%Y%m%d%H:%M:%S)
cd /tmp/
tar xf $1
name=$(tar tf $1 | head -1 | cut -d/ -f1)
echo ${name}/install.sh
if [ -f ./${name}/install.sh ]; then
    ./${name}/install.sh
elif [ $? -eq 0 ];then
    exit 0
else
    exit 1
fi