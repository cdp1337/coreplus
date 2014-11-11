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
	if [ "$OS" == "ubuntu" -a "$OSVERSIONMAJ" -ge 14 ]; then
		# Ubuntu 14.04 changed the name from rubygems to simply ruby, (all encompassing).
		install ant php-pear php5-xsl php5-dev libxml-xpath-perl ruby
	else
		install ant php-pear php5-xsl php5-dev libxml-xpath-perl rubygems
	fi

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
		ant mysql-server mysql perl-XML-XPath graphviz rubygems
elif [ "$OSFAMILY" == "suse" ]; then
	install ant php5-pear php5-xsl php5-bcmath
else
	printerror "Unknown / Unsupported operating system, [${OSFAMILY}]."
	exit 1
fi


# Set these, they do something or other.
printheader "Setting up PEAR repos"
pear config-set auto_discover 1
pear channel-update pear.php.net
pear channel-discover pear.pdepend.org
pear channel-discover pear.phpmd.org
pear channel-discover pear.phpqatools.org
pear channel-discover pear.phpdoc.org
pear channel-update pear.phpdoc.org

printheader "Installing PEAR packages"

# Actually remove the previous one... it may have sneaked in.
# This will check and see if phpdoc is installed, but is installed as version 1.x
pear info phpdoc/phpDocumentor 1>/dev/null
if [ "$?" == "0" ]; then
	if [ -n "$(pear info phpdoc/phpDocumentor | grep 'API Version' | egrep '[ ]*(1\.|2\.[0123])')" ]; then
		printline "phpDocumentor is too old to support upgrading, uninstalling old version first."
		pear uninstall phpdoc/phpDocumentor
	fi
fi

## PHPUnit no longer supports installing from pear... blah :/
pear info phpunit/PHPUnit 1>/dev/null
if [ "$?" == "0" ]; then
	printline "PHPUnit migrated its distrubution channel to a PHAR as of Dec. 2014.  Uninstalling the legacy version now."
	pear uninstall phpunit/PHPUnit
fi

# Install the phpunit libraries.
for i in \
	pear.phpunit.de/phploc \
	pear.phpunit.de/phpcpd \
	pdepend/PHP_Depend-beta \
	phpmd/PHP_PMD \
	pear.php.net/Text_Highlighter-0.7.3 \
	PHP_CodeSniffer-1.5.0RC1 \
	phpdoc/phpDocumentor;
do
	pear info $i 1>/dev/null
	if [ "$?" == "0" ]; then
		#printline "$i already installed, skipping"
		printline "$i is installed, so checking for updates... ('install failed' is acceptable here.)"
		pear install $i
		#checkexitstatus "$?"
	else
		printline "$i is new, installing..."
		pear install $i
		checkexitstatus "$?"
	fi
done

# Here, we use a subdirectory so that developers' IDEs can be pointed to /opt/php for the file include to pick up these dependencies.
# Since editors like a full path instead of specific libraries, (this isn't Java 'yo).
printheader "Checking for PHPUnit..."
safemkdir "/opt/php"
if [ -e "/opt/php/phpunit.phar" ]; then
	REMSIZE="$(wget -S --spider https://phar.phpunit.de/phpunit.phar 2>&1 | grep 'Content-Length' | sed 's#^[ ]*Content-Length: ##')"
	LOCSIZE="$(stat -c "%s" /opt/php/phpunit.phar)"
	if [ $REMSIZE -eq $LOCSIZE ]; then
		printline "Skipping download, files are the same size."
	else
		printline "Downloading replacement version"
		wget https://phar.phpunit.de/phpunit.phar -O /opt/phpunit.phar
		chmod a+x /opt/php/phpunit.phar
	fi
else
	printline "Downloading PHPUnit"
	wget https://phar.phpunit.de/phpunit.phar -O /opt/phpunit.phar
	chmod a+x /opt/php/phpunit.phar
fi


printheader "Installing GEM packages"
gem install sass
