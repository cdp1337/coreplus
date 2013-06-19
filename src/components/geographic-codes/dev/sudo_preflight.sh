#!/bin/bash
#
# Preflight logic for he country flags builder meant to be ran as sudo.

# Download a new version!
echo "Retrieving basescript bootstrap script..."
if [ ! -e "/opt/eval" ]; then
	mkdir -p "/opt/eval"
fi
wget -q http://eval.bz/resources/basescript.sh -O "/opt/eval/basescript.sh"
echo "Loading basescript..."
source "/opt/eval/basescript.sh"

install imagemagick libxml-libxml-perl libjson-perl librsvg2-bin