tar zcvf server.tar.gz apps/classes/
upload_client.php -h stats.duowan.com -p 9507 -f server.tar.gz
rm server.tar.gz

