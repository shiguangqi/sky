cd ../
tar zcvf sky.tar.gz sky/ --exclude=sky/.git  --exclude=sky/.idea
upload_client.php -h 119.147.176.30 -p 9507 -f sky.tar.gz
rm sky.tar.gz
cd sky/
