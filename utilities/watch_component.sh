#!/bin/bash
#
# Simple script to component for changes in the requested component, (specifically assets/scss), and recompile the stylesheets.
#
# Useful for component development!

COMPONENT="$1"
ROOTPDIR="$(readlink -f $(dirname $0)/../)"

echo "Loading basescript..."
source "/opt/eval/basescript.sh"

if [ -z "COMPONENT" ]; then
	printerror "Please include the component as the first argument."
	exit 1
fi

if [ -z "$(which inotifywait 2>/dev/null)" ]; then
	printerror "Unable to locate inotifywait!"
	printline "Please install inotify-tools first."
	if [ "$OSFAMILY" == "debian" ]; then
		printline "run the following command to install to necessary tools:"
		printline "sudo apt-get install inotify-tools"
	elif [ "$OSFAMILY" == "redhat" ]; then
		printline "run the following command to install to necessary tools:"
		printline "sudo yum install inotify-tools"
	elif [ "$OSFAMILY" == "suse" ]; then
		printline "run the following command to install to necessary tools:"
		printline "sudo yum install inotify-tools"
	fi
	exit 1
fi

printheader "Starting watch!"
while inotifywait -e modify -r $ROOTPDIR/src/components/$COMPONENT/assets/scss $ROOTPDIR/src/components/$COMPONENT/assets/js; do
	$ROOTPDIR/utilities/compiler.php --scss --component=$COMPONENT
	$ROOTPDIR/utilities/compiler.php --js --component=$COMPONENT
	$ROOTPDIR/utilities/reinstall.php --assets --component=$COMPONENT --verbosity=0
done