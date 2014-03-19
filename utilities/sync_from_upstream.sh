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


function sync_component() {
	CNAME="$1"

	echo "Syncing component $CNAME..."

	if [ -e "$BASEDIR/src/components/$CNAME/.upstreamignore" ]; then
		rsync $OPTIONS --exclude=.upstreamignore --exclude-from="$BASEDIR/src/components/$CNAME/.upstreamignore" \
		--delete "$UPSTREAM/src/components/$CNAME/" "$BASEDIR/src/components/$CNAME"
	else
		rsync $OPTIONS --delete "$UPSTREAM/src/components/$CNAME/" "$BASEDIR/src/components/$CNAME"
	fi
}

COMPONENTS="$(ls "$BASEDIR/src/components")"
OPTIONS="-rag"

echo "Syncing build scripts..."
rsync $OPTIONS --delete "$UPSTREAM/build/" "$BASEDIR/build"
rsync $OPTIONS "$UPSTREAM/build.xml" "$BASEDIR/"
rsync $OPTIONS "$UPSTREAM/ant.properties.ex" "$BASEDIR/"
rsync $OPTIONS "$UPSTREAM/build.xml" "$BASEDIR/"
rsync $OPTIONS "$UPSTREAM/.idea/codeStyleSettings.xml" "$BASEDIR/.idea/"

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
rsync $OPTIONS "$UPSTREAM/src/config/configuration.xml.ant" "$BASEDIR/src/config/"
rsync $OPTIONS "$UPSTREAM/src/htaccess.ex" "$BASEDIR/src/"
rsync $OPTIONS "$UPSTREAM/src/htaccess.ant" "$BASEDIR/src/"
rsync $OPTIONS "$UPSTREAM/src/index.php" "$BASEDIR/src/"

echo "Syncing core themes..."
#rsync $OPTIONS --delete "$UPSTREAM/src/themes/default/" "$BASEDIR/src/themes/default"
rsync $OPTIONS --delete "$UPSTREAM/src/themes/base-v2/" "$BASEDIR/src/themes/base-v2"

echo "Syncing core components..."
sync_component "phpwhois"
sync_component "geographic-codes"
sync_component "jquery-full"
sync_component "jquery-hoverintent"

for i in $COMPONENTS; do
	if [ -e "$UPSTREAM/src/components/$i" ]; then
		sync_component "$i"

	elif [ "$i" == "sitemap" ]; then
		# User has been migrated into Core as of 3.0.x
		echo "Deleting legacy component $i..."
		rm -fr "$BASEDIR/src/components/$i"

	elif [ "$i" == "user" ]; then
		# User has been migrated into Core as of 2.8.x
		echo "Deleting legacy component $i..."
		rm -fr "$BASEDIR/src/components/$i"

	elif [ "$i" == "googleanalytics" ]; then
		# This has been renamed to "google".
		echo "Deleting legacy component $i..."
		rm -fr "$BASEDIR/src/components/$i"

		sync_component "google"

	else
		echo "Skipping component $i, (does not exist in upstream)"
	fi
done

