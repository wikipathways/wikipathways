#!/bin/sh

## Downloads dependencies for the linkout script ##
## Run this before compiling the script with ant ##

CLIENT_FILE=$(wget -q -O - http://www.pathvisio.org/data/releases/current/ | grep -m 1 -o -E 'wikipathways_client_bin-[0-9]+\.[0-9]+\.[0-9]+-r[0-9]+\.tar\.gz' | head -n1)

wget -m -nd "http://www.pathvisio.org/data/releases/current/$CLIENT_FILE"

mkdir lib-wpclient
mkdir /tmp/extracted
tar xfz $CLIENT_FILE -C /tmp/extracted
find /tmp/extracted -type f -iname "*.jar" -exec mv -i {} lib-wpclient/ \;
rm -rf /tmp/extracted
