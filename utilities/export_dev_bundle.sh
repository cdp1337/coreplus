#!/bin/bash
#
# Script to export a developer-centric clone of this project.

BASEDIR="$(readlink -f $(dirname $0)/..)"

EXPORTDIR="$BASEDIR/exports/dev-bundles"
BASEEXPORTTGZ="$EXPORTDIR/dev-bundle-basic-$(date +%Y%m%d).tgz"
FULLEXPORTTGZ="$EXPORTDIR/dev-bundle-full-$(date +%Y%m%d).tgz"

if [ ! -e "$EXPORTDIR" ]; then
	mkdir -p "$EXPORTDIR"
fi

# Tar up everything, this will be the full build.
echo "Exporting full tarball..."
tar -czf "$FULLEXPORTTGZ" -C "$BASEDIR" \
docs LICENSES utilities vendor .gitignore ant.properties.ex build.xml README.md \
.idea/codeStyleSettings.xml .idea/php.xml \
src/components \
src/config/configuration.example.xml src/config/.htaccess \
src/core src/install src/themes src/htaccess.example src/index.php
echo "OK!  Created $FULLEXPORTTGZ"


# And tar up the essentials only.
echo "Exporting basic tarball..."
tar -czf "$BASEEXPORTTGZ" -C "$BASEDIR" \
docs LICENSES utilities vendor .gitignore ant.properties.ex build.xml README.md \
.idea/codeStyleSettings.xml .idea/php.xml \
src/components/codemirror src/components/content src/components/coolphpcaptcha src/components/cron \
src/components/facebook src/components/fontawesome \
src/components/geographic-codes src/components/google \
src/components/jquery-full src/components/jquery-file-upload src/components/jquery-hoverintent src/components/jsonjs \
src/components/navigation src/components/nonce \
src/components/phpwhois \
src/components/security-suite \
src/components/tinymce src/components/theme \
src/themes/base-v2 \
src/config/configuration.example.xml src/config/.htaccess \
src/core src/install src/htaccess.example src/index.php
echo "OK!  Created $BASEEXPORTTGZ"
