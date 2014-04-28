#!/bin/bash
#
# Script to export a production-oriented clone of this project.

BASEDIR="$(readlink -f $(dirname $0)/..)"

EXPORTDIR="$BASEDIR/exports/src-bundles"
FULLEXPORTTGZ="$EXPORTDIR/site-src-$(date +%Y%m%d).tgz"

if [ ! -e "$EXPORTDIR" ]; then
	mkdir -p "$EXPORTDIR"
fi

# Tar up everything, this will be the full build.
echo "Exporting full tarball..."
tar -czf "$FULLEXPORTTGZ" -C "$BASEDIR/src/" \
components \
config/configuration.xml.ex config/.htaccess \
core install themes htaccess.ex index.php utilities
echo "OK!  Created $FULLEXPORTTGZ"
