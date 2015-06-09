#!/bin/sh
#
# Fix line endings
#
# Simple script to run through the components, themes, and core to replace any and all \r\n newlines with \n.
# This is useful because Core is a unix-based project and having both formats tends to confuse the VCS systems a little.
#
# To run it, simply execute this script.  No arguments supported or needed.
#
# @author Charlie Powell <charlie@evalagency.com>
# @date 2013.05.15

BASEDIR="$(readlink -f $(dirname $0)/..)"
ROOTPDIR="$(readlink -f $(dirname $0)/../src)"

if [ -z "$(which dos2unix 2>/dev/null)" ]; then
	echo "Please install dos2unix first!"
	exit 1
fi

find $ROOTPDIR/components -name '*.css' -o -name '*.js' -o -name '*.php' -exec file '{}' \; \
	| grep 'CRLF line' \
	| while read i; do
		FILE="$(echo $i | sed 's#^\([^:]*\): .*$#\1#')";
		#echo "Converting $FILE to LF line endings...";
		dos2unix $FILE;
	done

find $ROOTPDIR/themes -name '*.css' -o -name '*.js' -o -name '*.php' -exec file '{}' \; \
	| grep 'CRLF line' \
	| while read i; do
		FILE="$(echo $i | sed 's#^\([^:]*\): .*$#\1#')";
		#echo "Converting $FILE to LF line endings...";
		dos2unix $FILE;
	done

find $ROOTPDIR/core -name '*.css' -o -name '*.js' -o -name '*.php' -exec file '{}' \; \
	| grep 'CRLF line' \
	| while read i; do
		FILE="$(echo $i | sed 's#^\([^:]*\): .*$#\1#')";
		#echo "Converting $FILE to LF line endings...";
		dos2unix $FILE;
	done