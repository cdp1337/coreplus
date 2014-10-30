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


# Patch bitters base, (since we are using Neat).
printheader "Patching bitters"
cat "$THEMEDIR/assets/scss/vendor/bitters/_base.scss" \
 | sed 's:^// @import "grid-settings":@import "grid-settings":' \
 | sed 's:^@import "variables":// @import "variables":' \
 > "$THEMEDIR/assets/scss/vendor/bitters/_base.scss.new"
rm -fr "$THEMEDIR/assets/scss/vendor/bitters/_base.scss"
mv "$THEMEDIR/assets/scss/vendor/bitters/_base.scss.new" "$THEMEDIR/assets/scss/vendor/bitters/_base.scss"


cat "$THEMEDIR/assets/scss/vendor/bitters/_grid-settings.scss" \
 | sed 's:^@import "neat-helpers":@import "../neat/neat-helpers":' \
 > "$THEMEDIR/assets/scss/vendor/bitters/_grid-settings.scss.new"
rm -fr "$THEMEDIR/assets/scss/vendor/bitters/_grid-settings.scss"
mv "$THEMEDIR/assets/scss/vendor/bitters/_grid-settings.scss.new" "$THEMEDIR/assets/scss/vendor/bitters/_grid-settings.scss"


printheader "Downloading newest normalize from github"
wget https://raw.githubusercontent.com/necolas/normalize.css/master/normalize.css -O "$THEMEDIR/assets/scss/vendor/_normalize.scss"