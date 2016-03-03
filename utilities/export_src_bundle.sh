#!/bin/bash
#
# Script to export a production-oriented clone of this project.

BASEDIR="$(readlink -f $(dirname $0)/..)"

EXPORTDIR="$BASEDIR/exports/src-bundles"
FULLEXPORTTGZ="$EXPORTDIR/site-src-$(date +%Y%m%d).tgz"

if [ ! -e "$EXPORTDIR" ]; then
	mkdir -p "$EXPORTDIR"
fi

# Remove old exports so that they do not clutter the filesystem.
# These aren't meant to be archival storage.
rm -fr $EXPORTDIR/*.tgz

# Tar up everything, this will be the full build.
echo "Exporting full tarball..."
tar -czf "$FULLEXPORTTGZ" -C "$BASEDIR/src/" \
components \
config/configuration.example.xml config/.htaccess \
core install themes htaccess.example index.php utilities
echo "OK!  Created $FULLEXPORTTGZ"
