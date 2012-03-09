#!/bin/bash

## Beta Script do not use in Production enviroment!
##
##


#http://www.scamnailer.info/files/contrib/ClamNailer-1.01.gz

#http://www.scamnailer.info/files/2/ScamNailer-2.10.gz


mkdir -p  /usr/local/ScamNailer

cd /usr/local/ScamNailer

wget http://www.scamnailer.info/files/2/ScamNailer-2.10.gz

/bin/gunzip ScamNailer-2.10.gz



chmod 755 /usr/local/ScamNailer/ScamNailer-*

cd /usr/local/sbin/

ln -s /usr/local/ScamNailer/ScamNailer-* ScamNailer

echo "01 * * * *  root /usr/bin/perl /usr/local/sbin/ScamNailer >> /dev/null 2>&1" > /etc/cron.d/ScamNailer.cron




