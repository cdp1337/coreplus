#!/bin/bash
#
# This script will download everything necessary for building Core Plus
# Suitable for use in a CI server or a development checkout.
#
# Please note, this does not apply to simply running the site, only for developing on Core Plus.

ROOTPDIR="$(realpath $(dirname $0))"



# Set this to 1 if you are currently developing the build system.
# This will skip the download, (since you are working on those files...)
BUILDDEV=0


if [ "$(whoami)" != "root" ]; then
	echo "Please run this script with sudo"
	exit 1
fi


if [ "$BUILDDEV" == "0" ]; then
	# Download a new version!
	echo "Retrieving basescript bootstrap script..."
	if [ ! -e "/opt/eval" ]; then
		mkdir -p "/opt/eval"
	fi
	wget -q http://eval.bz/resources/basescript.sh -O "/opt/eval/basescript.sh"
fi
echo "Loading basescript..."
source "/opt/eval/basescript.sh"


# Install the necessary dependencies
if [ "$OSFAMILY" == "debian" ]; then
	install ant php-pear php5-xsl php-dev
elif [ "$OSFAMILY" == "redhat" ]; then
	install ant php-pear php-xsl php-devel
fi


# Set these, they do something or other.
printheader "Setting up PEAR repos"
pear config-set auto_discover 1
pear channel-update pear.php.net
pear channel-discover pear.pdepend.org
pear channel-discover pear.phpmd.org
pear channel-discover pear.phpqatools.org
pear channel-discover pear.phpdoc.org

printheader "Installing PEAR packages"

# Install the phpunit libraries.
pear install pear.phpunit.de/phploc
pear install pear.phpunit.de/PHPUnit
pear install pear.phpunit.de/phpcpd

# Download pdepend - A small program that performs static code analysis on a given source base. 
pear install pdepend/PHP_Depend-beta

# Download phpmd - A spin-off project of PHP Depend and aims to be a PHP equivalent of the Java tool PMD
pear install --alldeps phpmd/PHP_PMD

pear install pear.php.net/Text_Highlighter-0.7.3

pear install --alldeps phpqatools/PHP_CodeBrowser

pear install PHP_CodeSniffer-1.5.0RC1

# Actually remove the previous one... it may have sneaked in.
pear info phpdoc/phpDocumentor 1>/dev/null
if [ "$?" == "0" ]; then
	if [ -z "$(pear info phpdoc/phpDocumentor | grep 'API Version' | grep '2.0')" ]; then
		pear uninstall phpdoc/phpDocumentor	
	fi
fi

pear install phpdoc/phpDocumentor-alpha
