#!/bin/bash

#test all components that have unit tests

echo "##############################";
echo "Running unit tests on all Components...";
echo "##############################";
find ../components/ -type d | while read D;
do
	if [ -e "$D/tests/" ]; then

		echo "##############################";
		echo "Testing $D";
		echo "##############################";

		$(dirname $0)/phpunit.phar $D/tests;
	fi
done;

echo "##############################";
echo "Running unit tests on Core...";
echo "##############################";
# also run tests on Core
$(dirname $0)/phpunit.phar ../core/tests;