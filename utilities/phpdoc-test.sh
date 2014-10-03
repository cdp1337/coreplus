#!/bin/bash
#
# Build one file using phpdoc and preview it in a browser.
#
# Since building the entirity of Core can take some time,
# this is useful for testing a single file with changes.

if [ -z "$1" ]; then
	echo "Usage: $0 [filename to build]"
	echo "to build one file and preview it using the default browser."
	exit 1
fi

phpdoc -f "$1" -t /tmp/phpdoc --title "Test PHPDoc Build"
xdg-open /tmp/phpdoc/index.html