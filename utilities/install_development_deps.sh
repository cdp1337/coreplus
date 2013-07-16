#!/bin/bash
#
# This script will download everything necessary for building Core Plus
# Suitable for use in a CI server or a development checkout.
#
# Please note, this does not apply to simply running the site, only for developing on Core Plus.

ROOTPDIR="$(readlink -f $(dirname $0))"



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
	install ant php-pear php5-xsl php5-dev libxml-xpath-perl
elif [ "$OSFAMILY" == "redhat" ]; then
	# RH based distros need some updates to utilize 3rd party projects.
	if [ "$OSVERSIONMAJ" == "6" ]; then
		rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
		rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
	elif [ "$OSVERSIONMAJ" == "5" ]; then
		rpm -Uvh http://dl.fedoraproject.org/pub/epel/5/i386/epel-release-5-4.noarch.rpm
		rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-5.rpm
	fi

	yum --enablerepo=remi,remi-test install -y \
		php php-common php-devel php-xsl php-mbstring php-pear php-mysql php-gd php-xdebug \
		ant mysql-server mysql perl-XML-XPath graphviz
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
for i in \
	pear.phpunit.de/phploc \
	phpunit/PHPUnit \
	pear.phpunit.de/phpcpd \
	pdepend/PHP_Depend-beta \
	phpmd/PHP_PMD \
	pear.php.net/Text_Highlighter-0.7.3 \
	PHP_CodeSniffer-1.5.0RC1;
do
	pear info pear.phpunit.de/phploc 1>/dev/null
	if [ "$?" == "0" ]; then
		printline "$i already installed, skipping"
	else
		printline "$i is new, installing..."
		pear install $i
		checkexitstatus "$?"
	fi
done

# Actually remove the previous one... it may have sneaked in.
# This will check and see if phpdoc is installed, but is installed as version 1.x
pear info phpdoc/phpDocumentor 1>/dev/null
if [ "$?" == "0" ]; then
	if [ -z "$(pear info phpdoc/phpDocumentor | grep 'API Version' | grep '2.0')" ]; then
		pear uninstall phpdoc/phpDocumentor	
	fi
fi

# Now I can check/install v2!
pear info phpdoc/phpDocumentor 1>/dev/null
if [ "$?" == "0" ]; then
	printline "phpdoc/phpDocumentor-alpha already installed, skipping"
else
	printline "phpdoc/phpDocumentor-alpha is new, installing..."
	pear install phpdoc/phpDocumentor-alpha
	checkexitstatus "$?"
fi
