#!/bin/sh
sudo sh -c "./cls.sh"
omxplayer "$1" < "FIFO_FILE_NAME" > /dev/null 2>&1 &
sleep 1
echo -n . > "FIFO_FILE_NAME"
