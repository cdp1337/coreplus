#!/bin/bash
#
# Simple script to build the iso-country-flags-svg-collection lib.
#
# Requires the following packages on the base system:
#   imagemagick libxml-libxml-perl libjson-perl librsvg2-bin optipng

COREDIR="$(readlink -f $(dirname $0)/../../)"
HERE="$COREDIR/dev/iso-country-flags-svg-collection"

if [ -e "$COREDIR/assets/images/iso-country-flags" ]; then
	# Reset to clean!
	rm -fr "$COREDIR/assets/images/iso-country-flags"
fi
mkdir "$COREDIR/assets/images/iso-country-flags"

if [ ! -e "$HERE/build" ]; then
	mkdir "$HERE/build"
fi

CWD="$(pwd)"
cd "$HERE"
scripts/build.pl --cmd svg2png --json iso-3166-1.json --res 128x96 --svgs svg/country-4x3/ --out build

# Move them to the correct destination
mv build/png-country-4x3/res-128x96/*.png $COREDIR/assets/images/iso-country-flags/

# $COREDIR/assets/images/iso-country-flags/
#pngcrush -q -d $COREDIR/assets/images/iso-country-flags

cd "$CWD"