#!/bin/sh
sudo sh -c "./cls.sh"
omxplayer "$1" < $2 > /dev/null 2>&1 &
sleep 1
echo -n . > $2
