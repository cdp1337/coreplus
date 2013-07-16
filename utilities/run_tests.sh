#!/bin/bash

BASEDIR="$(readlink -f $(dirname $0)/..)"
ROOTPDIR="$(readlink -f $(dirname $0)/../src)"

# Where is PHPUnit at?
EXEC="$(which phpunit)"
#EXEC="$BASEDIR/vendor/phpunit.phar"


OPTS="--colors"
#OPTS="--debug --verbose"


function perform_test (){
	TESTDIR="$1"

	echo "";
	echo "##############################################";
	echo "##  Testing $TESTDIR";

	"$EXEC" $OPTS --bootstrap "$BASEDIR/utilities/phpunit-loader.php" "$TESTDIR"
}


# If a specific component is requested, run the tests on only that location.
if [ "$1" != "" ]; then
	# Single always gets remapped to verbose.
	#OPTS="--debug --verbose"
	OPTS="--debug --colors"

	if [ "$1" == "core" ]; then
		perform_test "$ROOTPDIR/core"
	else
		perform_test "$ROOTPDIR/components/$1"
	fi
else
	# Just run tests on the entire codebase.
	perform_test "$ROOTPDIR"
fi
