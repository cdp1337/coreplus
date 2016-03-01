#!/bin/bash
#
# Simple script to watch for changes in the requested theme, (specifically assets/scss), and recompile the stylesheets.
#
# Useful for theme development!

THEME="$1"
ROOTPDIR="$(readlink -f $(dirname $0)/../)"

echo "Loading basescript..."
source "/opt/eval/basescript.sh"

if [ -z "$THEME" ]; then
	printerror "Please include the theme as the first argument."
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
while inotifywait -e modify -r $ROOTPDIR/src/themes/$THEME/assets/scss; do
	$ROOTPDIR/utilities/compiler.php --scss --theme=$THEME
	$ROOTPDIR/utilities/reinstall.php --assets --theme=$THEME --verbosity=0
done