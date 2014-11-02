#!/bin/bash
#
# Script to update all themes/base-v3/vendor scripts.
#

echo "Loading basescript..."
source "/opt/eval/basescript.sh"

BASEDIR="$(readlink -f $(dirname $0)/../../..)"
THEMEDIR="$BASEDIR/themes/base-v3"


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

printheader "Checking/Downloading latest bourbon from github"
downloadgit https://github.com/thoughtbot/bourbon.git
if [ -e "$THEMEDIR/assets/scss/vendor/bourbon" ]; then
	rm -fr "$THEMEDIR/assets/scss/vendor/bourbon"
fi
mkdir "$THEMEDIR/assets/scss/vendor/bourbon"
cp -r /tmp/https-github.com-thoughtbot-bourbon.git/dist/* "$BASEDIR/themes/base-v3/assets/scss/vendor/bourbon/"

printheader "Checking/Downloading latest neat from github"
downloadgit https://github.com/thoughtbot/neat.git
if [ -e "$THEMEDIR/assets/scss/vendor/neat" ]; then
	rm -fr "$THEMEDIR/assets/scss/vendor/neat"
fi
mkdir "$THEMEDIR/assets/scss/vendor/neat"
cp -r /tmp/https-github.com-thoughtbot-neat.git/app/assets/stylesheets/* "$THEMEDIR/assets/scss/vendor/neat/"

printheader "Checking/Downloading latest bitters from github"
downloadgit https://github.com/thoughtbot/bitters.git
if [ -e "$THEMEDIR/assets/scss/vendor/bitters" ]; then
	rm -fr "$THEMEDIR/assets/scss/vendor/bitters"
fi
mkdir "$THEMEDIR/assets/scss/vendor/bitters"
cp -r /tmp/https-github.com-thoughtbot-bitters.git/app/assets/stylesheets/* "$THEMEDIR/assets/scss/vendor/bitters/"


printheader "Downloading newest normalize from github"
wget https://raw.githubusercontent.com/necolas/normalize.css/master/normalize.css -O "$THEMEDIR/assets/scss/vendor/_normalize.scss"


printheader "Applying local patches"
for i in "$(find $THEMEDIR/dev/vendor-patches/ -type f -name '*.diff')"; do
	patch -p0 < "$i"
done