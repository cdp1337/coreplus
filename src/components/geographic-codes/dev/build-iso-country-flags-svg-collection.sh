#!/bin/bash
#
# Simple script to import and build the iso-country-flags-svg-collection lib, retrieving from the github source.
# This script is slightly more involving because it needs to perform some package installations.

HERE="$(readlink -f $(dirname $0)/../)"
BASEPDIR="$(readlink -f $(dirname $0)/../../../../)"

sudo "$HERE/dev/sudo_preflight.sh"

source "/opt/eval/basescript.sh"


if [ -e "$HERE/dev/iso-country-flags-svg-collection" ]; then
	rm -fr "$HERE/dev/iso-country-flags-svg-collection"
fi
#if [ -e "$HERE/assets" ]; then
#	rm -fr "$HERE/assets"
#fi

#mkdir -p "$HERE/assets/js/chart-js"

download git://github.com/koppi/iso-country-flags-svg-collection.git "$HERE/dev/iso-country-flags-svg-collection"
rm -fr "$HERE/dev/iso-country-flags-svg-collection/.git"
rm -fr "$HERE/dev/iso-country-flags-svg-collection/.gitignore"

cd "$HERE/dev/iso-country-flags-svg-collection"
printheader "Building icon collections..."
scripts/build.pl --cmd svg2png --json iso-3166-1.json --out "$HERE/assets/images/iso-country-flags" --res 640x480 --svgs svg/country-4x3/
checkexitstatus "$?"
cd -

exit 1;

mv "$HERE/lib/Chart.js" "$HERE/assets/js/chart-js/"
mv "$HERE/lib/Chart.min.js" "$HERE/assets/js/chart-js/"
echo "Chart.js and Chart.min.js have been moved to assets/js/chart-js for compatibility with Core." > "$HERE/lib/where-is-chart-js"

VERSION=$(cat "$HERE/lib/component.json" | egrep '[ ]*"version' | sed 's:.*"\(.*\)",$:\1:');


$BASEPDIR/utilities/packager.php -r -c chart-js

echo "Checked out version $VERSION of Chart-js :)"