#!/bin/bash

BASEDIR="$(readlink -f $(dirname $0)/..)"

# Get the upstream source.
UPSTREAM="$(cat "$BASEDIR/ant.properties" | egrep '^upstream=' | sed 's:^[^=]*=\(.*\):\1:')"

if [ -z "$UPSTREAM" ]; then
	echo "Please set the upstream directory in your properties.ant file!"
	echo ""
	echo "The line should look like:"
	echo "upstream=/path/to/core/upstream"
	echo ""
	echo "Which points to the top-level directory containing utilities/, src/, etc."
	exit 1
fi

if [ ! -e "$UPSTREAM" ]; then
	echo "$UPSTREAM does not seem to exist!"
	exit 1
fi

COMPONENTS="$(ls "$BASEDIR/src/components")"
OPTIONS="-rag"

echo "Syncing build scripts..."
rsync $OPTIONS --delete "$UPSTREAM/build/" "$BASEDIR/build"
rsync $OPTIONS "$UPSTREAM/build.xml" "$BASEDIR/"
rsync $OPTIONS "$UPSTREAM/ant.properties.ex" "$BASEDIR/"
rsync $OPTIONS "$UPSTREAM/build.xml" "$BASEDIR/"

echo "Syncing vendor..."
rsync $OPTIONS --delete "$UPSTREAM/vendor/" "$BASEDIR/vendor"

echo "Syncing utilities..."
rsync $OPTIONS "$UPSTREAM/utilities/" "$BASEDIR/utilities"

echo "Syncing docs..."
rsync $OPTIONS "$UPSTREAM/docs/" "$BASEDIR/docs"

#rsync $OPTIONS "$UPSTREAM/exports/" "$BASEDIR/exports"


echo "Syncing core..."
rsync $OPTIONS --delete "$UPSTREAM/src/core/" "$BASEDIR/src/core"
rsync $OPTIONS --delete "$UPSTREAM/src/install/" "$BASEDIR/src/install"
rsync $OPTIONS "$UPSTREAM/src/config/configuration.xml.ex" "$BASEDIR/src/config/"
rsync $OPTIONS "$UPSTREAM/src/htaccess.ex" "$BASEDIR/src/"
rsync $OPTIONS "$UPSTREAM/src/index.php" "$BASEDIR/src/"

echo "Syncing core themes..."
#rsync $OPTIONS --delete "$UPSTREAM/src/themes/default/" "$BASEDIR/src/themes/default"
rsync $OPTIONS --delete "$UPSTREAM/src/themes/base-v2/" "$BASEDIR/src/themes/base-v2"


for i in $COMPONENTS; do
	if [ -e "$UPSTREAM/src/components/$i" ]; then
		echo "Syncing  component $i..."

		if [ -e "$BASEDIR/src/components/$i/.upstreamignore" ]; then
			rsync $OPTIONS --exclude=.upstreamignore --exclude-from="$BASEDIR/src/components/$i/.upstreamignore" \
			--delete "$UPSTREAM/src/components/$i/" "$BASEDIR/src/components/$i"
		else
			rsync $OPTIONS --delete "$UPSTREAM/src/components/$i/" "$BASEDIR/src/components/$i"
		fi

	elif [ "$i" == "user" ]; then
		# User has been migrated into Core as of 2.8.x
		echo "Deleting legacy component $i..."
		rm -fr "$BASEDIR/src/components/$i"
	else
		echo "Skipping component $i, (does not exist in upstream)"
	fi
done

