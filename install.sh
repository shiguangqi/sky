DATE=$(date +%Y%m%d%H:%M:%S)
cd /tmp/
sudo tar zxvf sky.tar.gz
sudo mv /data/webroot/sky.duowan.com /data/webroot/sky.duowan.com.${DATE}
sudo mv sky /data/webroot/sky.duowan.com
cd -