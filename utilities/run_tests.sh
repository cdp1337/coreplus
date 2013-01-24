#!/bin/bash

ROOTPDIR="$(dirname $0)/.."

#test all components that have unit tests

echo "##############################";
echo "Running unit tests on all Components...";
echo "##############################";
find $ROOTPDIR/components/ -type d | while read D;
do
	if [ -d "$D/tests/" ]; then

		echo "##############################";
		echo "Testing $D";
		echo "##############################";

		$(dirname $0)/phpunit.phar --bootstrap $(dirname $0)/phpunit-loader.php --verbose --debug $D/tests
	fi
done;

echo "##############################";
echo "Running unit tests on Core...";
echo "##############################";
# also run tests on Core
$(dirname $0)/phpunit.phar $ROOTPDIR/core/tests;