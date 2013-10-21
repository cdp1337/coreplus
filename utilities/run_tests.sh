#!/bin/bash
#
# Simple script to run a collection of tests on the site with PHPUnit.
#
# This is NOT used by ant, but instead is useful for the developer when writing the tests,
# as it can execute a specific test without waiting for the others to complete.
#
# Usage:
#
# run_tests.sh (no arguments)
#    - Run every test suite on the system, mimicking what the ant utility does.
#
# run_tests.sh core
#    - Run all the *core* tests
#
# run_tests.sh name_of_component
#    - Run all tests in the "name_of_component" component
#
# run_tests.sh src/path/to/test/SomethingToTest.php
#    - Run the specific test file only

BASEDIR="$(readlink -f $(dirname $0)/..)"
ROOTPDIR="$(readlink -f $(dirname $0)/../src)"

# Where is PHPUnit at?
EXEC="$(which phpunit)"
#EXEC="$BASEDIR/vendor/phpunit.phar"


OPTS="--colors"


function perform_test (){
	TESTDIR="$1"

	echo "";
	echo "##############################################";
	echo "##  Testing $TESTDIR";

	"$EXEC" $OPTS --bootstrap "$BASEDIR/utilities/phpunit-loader.php" "$TESTDIR"
}


# If a specific component is requested, run the tests on only that location.
if [ "$1" != "" ]; then
	# Tack on debug mode
	OPTS="$OPTS --debug"

	# Is this a specific file?
	if [ -e "$BASEDIR/$1" ]; then
		perform_test "$BASEDIR/$1"
	elif [ "$1" == "core" ]; then
		perform_test "$ROOTPDIR/core"
	else
		perform_test "$ROOTPDIR/components/$1"
	fi
else
	# Just run tests on the entire codebase.
	perform_test "$ROOTPDIR"
fi
