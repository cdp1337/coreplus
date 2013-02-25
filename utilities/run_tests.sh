#!/bin/bash

ROOTPDIR="$(dirname $0)/.."

#test all components that have unit tests

echo "##############################";
echo "Running unit tests on all Components...";
echo "##############################";
find $ROOTPDIR/components/ -type d | while read D;
do
	if [ -d "$D/tests/" ]; then

		echo "";
		echo "##############################################";
		echo "##  Testing $D";

		$(dirname $0)/phpunit.phar --bootstrap $(dirname $0)/phpunit-loader.php --verbose --debug $D/tests
	fi
done;

echo "";
echo "##############################################";
echo "##  Running unit tests on Core...";
# also run tests on Core
$(dirname $0)/phpunit.phar --bootstrap $(dirname $0)/phpunit-loader.php --verbose --debug $ROOTPDIR/core/tests;