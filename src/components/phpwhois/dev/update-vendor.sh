#!/bin/bash
#
# Script to update all themes/base-v3/vendor scripts.
#

echo "Loading basescript..."
source "/opt/eval/basescript.sh"

BASEDIR="$(readlink -f $(dirname $0)/../../..)"
DESTDIR="$(readlink -f $(dirname $0)/..)"


function downloadgit (){
	SRC="$1"
	DIR="/tmp/$(echo $SRC | sed 's:[^a-z0-9\.]:-:g' | sed 's:\-\-\-:-:g')/"

	if [ -e "$DIR" ]; then
		cd "$DIR"
		git pull
		cd -
	else
		git clone "$SRC" "$DIR"
	fi
}

printheader "Checking/Downloading latest phpwhois from git.eval.bz"
downloadgit git@git.eval.bz:eval/phpwhois.git
if [ -e "$DESTDIR/lib/phpwhois" ]; then
	rm -fr "$DESTDIR/lib/phpwhois"
fi
mkdir "$DESTDIR/lib/phpwhois"
cp -r /tmp/git-git.eval.bz-eval-phpwhois.git/src/phpwhois/* "$DESTDIR/lib/phpwhois/"
cp -r /tmp/git-git.eval.bz-eval-phpwhois.git/tests/* "$DESTDIR/tests/"
cp -r /tmp/git-git.eval.bz-eval-phpwhois.git/README.md "$DESTDIR/"
cp -r /tmp/git-git.eval.bz-eval-phpwhois.git/LICENSE "$DESTDIR/"


#printheader "Applying local patches"
#find $THEMEDIR/dev/vendor-patches/ -type f -name '*.diff' | while read i; do
#	patch -p0 < "$i"
#done