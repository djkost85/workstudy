#!/bin/bash
DIR=/var/www/backup
DT=$(date +%m-%d)
H=$(date +%Y-%m-%d_%H-%M)
TARGETNOW=$DIR/$DT

if [ ! -d $TARGETNOW ]; then
        mkdir -p "$TARGETNOW"
fi
cd "$TARGETNOW"

/usr/bin/mysqldump --user=root --password='$apr1$BBn657Se' --host='localhost' clb001 | gzip > clb001_staging_"$H".sql.gz &

cd /var/www/backup

find . -mtime +7 -maxdepth 1 -exec rm -rf {} \;

