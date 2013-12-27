#!/bin/bash
# 
# Minify a js file using Yahoo's yuicompressor
# and prepend the file header and file version.
# This is basic information to help the developer keep the file insync.
# 
# @author Charlie Powell <charlie@eval.bz>

BASEDIR="$(readlink -f $(dirname $0)/..)"
YUICOMPRESSOR="$BASEDIR/vendor/yuicompressor-2.4.8.jar"

function help {
	echo "Usage: $0 '/path/to/fullcode.js'"
	echo "Give the filename you wish to minify as the one and only argument."
	echo "That's it!"
	echo ""
	echo "A file will be created/overwrote with the filename /path/to/fullcode.min.js"
}

FILE="$1"
if [ -z "$FILE" ]; then
	help
	exit 0
fi

EXT=$(echo $FILE | awk -F . '{print $NF}')
BASENAME=$(basename $FILE .$EXT)
DIRNAME=$(dirname $FILE)

SRCFILE="$DIRNAME/$BASENAME.$EXT"
DSTFILE="$DIRNAME/$BASENAME.min.$EXT"

if [ ! -e "$SRCFILE" ]; then
	echo "Error: Unable to locate requested file [$SRCFILE]" >&2
	exit 1
fi

# Pull the header from the first comment line in the file.
HEADER=$(head $SRCFILE -n10 | egrep '^[ ]*\*[ ]+' | sed 's:^[ ]*\*[ ]*::' | egrep '^[^@]' | head -n1)

# The version should be * @version (phpDoc style)
VERSION=$(head $SRCFILE -n20 | egrep '^[ ]*\*[ ]+' | sed 's:^[ ]*\*[ ]*::' | egrep '^@version' | head -n1)

# Versions can also just be * v x.y.z, since that's the jquery style.
if [ "$VERSION" == "" ]; then
	VERSION=$(head $SRCFILE -n20 | egrep '^[ ]*\*[ ]+' | sed 's:^[ ]*\*[ ]*::' | egrep '^v [0-9]' | head -n1)
fi


# Clear out the destination file
echo -n "" > $DSTFILE
if [ $? != 0 ]; then
	echo "Error: Unable to write to destination file [$DSTFILE]" >&2
	exit 1
fi

if [ -n "$HEADER" ]; then
	# Write the header only if it's nonblank
	echo "// $HEADER" >> $DSTFILE
fi

if [ -n "$VERSION" ]; then
	# Write the version only if it's nonblank
	echo "// $VERSION" >> $DSTFILE
fi

# Finally, do the actual minifying.
java -jar $YUICOMPRESSOR -v $SRCFILE >> $DSTFILE

S1=$(stat -c%s "$SRCFILE")
S2=$(stat -c%s "$DSTFILE")
RATIO=$(echo "100 - ( ($S1 - $S2) * 100 / $S1 )" | bc -q)

echo "Wrote minified code to [$DSTFILE] at a compression of $RATIO%"
