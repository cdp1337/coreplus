#!/bin/bash
#
# Simple script to import and build the facebook component, retrieving from the github source.

echo "Loading basescript..."
source "/opt/eval/basescript.sh"

# One directory up from the dev is the component root.
COMPONENTDIR="$(readlink -f $(dirname $0)/../)"
# We're in the src directory anyway, only 3 up from here.
ROOTPDIR="$(readlink -f $(dirname $0)/../../../)"
# And dev is located 4 directories down from the root.
BASEPDIR="$(readlink -f $(dirname $0)/../../../../)"


safemkdir "$COMPONENTDIR/libs"

if [ -e "$COMPONENTDIR/libs/facebook-php-sdk" ]; then
	PVERSION=$(egrep '^Facebook PHP SDK \(v' "$COMPONENTDIR/libs/facebook-php-sdk/readme.md" | sed 's:.*(v\.\(.*\)):\1:');
	rm -fr "$COMPONENTDIR/libs/facebook-php-sdk"
else
	PVERSION=""
fi


download git://https://github.com/facebook/facebook-php-sdk.git "$COMPONENTDIR/libs/facebook-php-sdk"
rm -fr "$COMPONENTDIR/libs/facebook-php-sdk/.git"
rm -fr "$COMPONENTDIR/libs/facebook-php-sdk/.gitignore"

# Facebook PHP SDK (v.3.2.0)
VERSION=$(egrep '^Facebook PHP SDK \(v' "$COMPONENTDIR/libs/facebook-php-sdk/readme.md" | sed 's:.*(v\.\(.*\)):\1:');

$BASEPDIR/utilities/packager.php -r -c facebook

if [ "$PVERSION" != "$VERSION" ]; then
	printline "Upgraded Facebook from $PVERSION to $VERSION"
fi
