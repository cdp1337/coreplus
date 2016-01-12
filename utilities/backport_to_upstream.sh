#!/bin/bash
#
# Script to backport changes to upstream.

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


function sync_component() {
	CNAME="$1"

	echo "Syncing component $CNAME..."

	if [ -e "$BASEDIR/src/components/$CNAME/.upstreamignore" ]; then
		rsync $OPTIONS --exclude=.upstreamignore --exclude-from="$BASEDIR/src/components/$CNAME/.upstreamignore" \
		--delete "$BASEDIR/src/components/$CNAME/" "$UPSTREAM/src/components/$CNAME"
	else
		rsync $OPTIONS --delete "$BASEDIR/src/components/$CNAME/" "$UPSTREAM/src/components/$CNAME"
	fi
}

COMPONENTS="$(ls "$UPSTREAM/src/components")"
OPTIONS="-rag"

echo "Syncing build scripts..."
rsync $OPTIONS --delete "$BASEDIR/build/" "$UPSTREAM/build"
rsync $OPTIONS "$BASEDIR/build.xml" "$UPSTREAM/"
rsync $OPTIONS "$BASEDIR/ant.properties.ex" "$UPSTREAM/"
rsync $OPTIONS "$BASEDIR/build.xml" "$UPSTREAM/"
rsync $OPTIONS "$BASEDIR/.gitignore" "$UPSTREAM/"
rsync $OPTIONS "$BASEDIR/.idea/codeStyleSettings.xml" "$UPSTREAM/.idea/"

echo "Syncing vendor..."
rsync $OPTIONS --delete "$BASEDIR/vendor/" "$UPSTREAM/vendor"

echo "Syncing utilities..."
rsync $OPTIONS "$BASEDIR/utilities/" "$UPSTREAM/utilities"

echo "Syncing docs..."
rsync $OPTIONS "$BASEDIR/docs/" "$UPSTREAM/docs"

#rsync $OPTIONS "$UPSTREAM/exports/" "$BASEDIR/exports"


echo "Syncing core..."
rsync $OPTIONS --delete "$BASEDIR/src/core/" "$UPSTREAM/src/core"
rsync $OPTIONS --delete "$BASEDIR/src/install/" "$UPSTREAM/src/install"
rsync $OPTIONS "$BASEDIR/src/config/configuration.example.xml" "$UPSTREAM/src/config/"
rsync $OPTIONS "$BASEDIR/src/htaccess.example" "$UPSTREAM/src/"
rsync $OPTIONS "$BASEDIR/src/robots.txt" "$UPSTREAM/src/"
rsync $OPTIONS "$BASEDIR/src/index.php" "$UPSTREAM/src/"
rsync $OPTIONS "$BASEDIR/src/utilities" "$UPSTREAM/src/"

echo "Syncing core themes..."
#rsync $OPTIONS --delete "$UPSTREAM/src/themes/default/" "$BASEDIR/src/themes/default"
#rsync $OPTIONS --delete "$UPSTREAM/src/themes/base-v2/" "$BASEDIR/src/themes/base-v2"
rsync $OPTIONS --delete "$BASEDIR/src/themes/base-v3/" "$UPSTREAM/src/themes/base-v3"

echo "Syncing core components..."
for i in $COMPONENTS; do
	if [ -e "$BASEDIR/src/components/$i" ]; then
		sync_component "$i"

	else
		echo "Skipping component $i, (does not exist in base)"
	fi
done

