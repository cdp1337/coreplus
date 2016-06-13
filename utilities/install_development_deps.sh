#!/bin/bash
#
# This script will download everything necessary for building Core Plus
# Suitable for use in a CI server or a development checkout.
#
# Please note, this does not apply to simply running the site, only for developing on Core Plus.

ROOTPDIR="$(readlink -f $(dirname $0))"

if [ "$(whoami)" != "root" ]; then
	echo "Please run this script with sudo"
	exit 1
fi


# Download a new version!
echo "Retrieving basescript bootstrap script..."
if [ ! -e "/opt/eval" ]; then
	mkdir -p "/opt/eval"
fi
wget -q https://eval.agency/resources/basescript.sh -O "/opt/eval/basescript.sh"
echo "Loading basescript..."
source "/opt/eval/basescript.sh"


# Install the necessary dependencies
if [ "$OS" == "ubuntu" ]; then
	# Ubuntu-specific install instructions
	if [ "$OSVERSIONMAJ" -ge 15 ]; then
		# Ubuntu 15.04 requires the nd library for phpmysql.
		install ant php-pear php5-xsl php5-dev libxml-xpath-perl ruby pngcrush php5-mysqlnd php5-mcrypt php5-curl php5-gd
		a2enmod rewrite
	elif [ "$OSVERSIONMAJ" -ge 14 ]; then
		# Ubuntu 14.04 changed the name from rubygems to simply ruby, (all encompassing).
		install ant php-pear php5-xsl php5-dev libxml-xpath-perl ruby pngcrush
	else
		install ant php-pear php5-xsl php5-dev libxml-xpath-perl rubygems pngcrush
	fi
elif [ "$OSFAMILY" == "debian" ]; then
	if [ "$OSVERSIONMAJ" -ge 9 ]; then
		# Use the native driver in stretch and above.
		# Also install PHP 7.x instead of 5.x by default!
		install ant php-pear php7.0-xsl php7.0-dev libxml-xpath-perl ruby pngcrush php7.0-mysql php7.0-mcrypt php7.0-curl php7.0-gd \
		    libapache2-mod-php7.0 mariadb-client-10.0 mariadb-server-10.0 php-xdebug graphviz
		a2enmod rewrite
		a2enmod php7.0

		if [ ! -e "/etc/php/7.0/mods-available/xdebug.ini" ]; then
			# Ensure that xdebug is linked to the correct directory, (it wasn't there by default)
			cat > /etc/php/7.0/mods-available/xdebug.ini << EOD
zend_extension=xdebug.so
xdebug.remote_enable=true
xdebug.remote_port=9000
xdebug.remote_autostart=1
EOD
			ln -s /etc/php/7.0/mods-available/xdebug.ini /etc/php/7.0/apache2/conf.d/20-xdebug.ini
			ln -s /etc/php/7.0/mods-available/xdebug.ini /etc/php/7.0/cli/conf.d/20-xdebug.ini
		fi
	else
		# Debian 8 (Jessie) and lower.
		install ant php-pear php5-xsl php5-dev libxml-xpath-perl rubygems pngcrush
	fi

elif [ "$OSFAMILY" == "redhat" ]; then
	# RH based distros need some updates to utilize 3rd party projects.
	if [ "$OSVERSIONMAJ" == "7" ]; then
		if [ ! -e /etc/yum.repos.d/MariaDB.repo ]; then
			# Install the MariaDB repo!
			echo "# MariaDB 10.0 CentOS repository list - created 2015-07-20 23:12 UTC
# http://mariadb.org/mariadb/repositories/
[mariadb]
name = MariaDB
baseurl = http://yum.mariadb.org/10.0/centos7-amd64
gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB
gpgcheck=1" > /etc/yum.repos.d/MariaDB.repo
		fi
		install epel-release
		yum update -y
		install httpd php php-mysqlnd php-mbstring php-xml php-soap php-mcrypt php-gd php-curl libmcrypt-devel MariaDB-server MariaDB-client
	elif [ "$OSVERSIONMAJ" == "6" ]; then
		rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
		rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
	elif [ "$OSVERSIONMAJ" == "5" ]; then
		rpm -Uvh http://dl.fedoraproject.org/pub/epel/5/i386/epel-release-5-4.noarch.rpm
		rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-5.rpm
	fi

	yum --enablerepo=remi,remi-test install -y \
		php php-common php-devel php-xsl php-mbstring php-pear php-mysql php-gd php-xdebug \
		ant mysql-server mysql perl-XML-XPath graphviz rubygems pngcrush
elif [ "$OSFAMILY" == "suse" ]; then
	install ant php5-pear php5-xsl php5-bcmath pngcrush
else
	printerror "Unknown / Unsupported operating system, [${OSFAMILY}]."
	echo "We'd love your contibution for this operating system!"
	echo "Post to https://github.com/nicholasryan/CorePlus/issues your howto and help out the project."
	exit 1
fi


# Set these, they do something or other.
printheader "Setting up PEAR repos"
pear config-set auto_discover 1
pear channel-update pear.php.net
pear channel-discover pear.pdepend.org
pear channel-discover pear.phpmd.org
#pear channel-discover pear.phpqatools.org
#pear channel-discover pear.phpdoc.org
#pear channel-update pear.phpdoc.org


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

pear info pear.phpunit.de/phploc 1>/dev/null
if [ "$?" == "0" ]; then
	printline "PHPUnit migrated its distrubution channel to a PHAR as of Dec. 2014.  Uninstalling the legacy version now."
	pear uninstall pear.phpunit.de/phploc
fi

pear info pear.phpunit.de/phpcpd 1>/dev/null
if [ "$?" == "0" ]; then
	printline "PHPUnit migrated its distrubution channel to a PHAR as of Dec. 2014.  Uninstalling the legacy version now."
	pear uninstall pear.phpunit.de/phpcpd
fi


## XHProf has not been supported in some time, removing from all deployments!
# Check and see if xhprof is installed.
# This is extremely useful in development!
#pecl info xhprof 1>/dev/null
#if [ "$?" == "1" ]; then
#	pecl install channel://pecl.php.net/xhprof-0.9.4
#	if [ ! -e "/etc/php5/mods-available/xhprof.ini" ]; then
#		cat > /etc/php5/mods-available/xhprof.ini << EOD
#[xhprof]
#extension=xhprof.so
#xhprof.output_dir="/var/tmp/xhprof"
#EOD
#		cat > /etc/apache2/conf-available/xhprof.conf << EOD
#Alias /xhprof /usr/share/php/xhprof_html
#
#<Directory /usr/share/php/xhprof_html>
#    DirectoryIndex index.php
#
#    <IfModule mod_php5.c>
#        <IfModule mod_mime.c>
#            AddType application/x-httpd-php .php
#        </IfModule>
#        <FilesMatch ".+\.php$">
#            SetHandler application/x-httpd-php
#        </FilesMatch>
#
#        php_flag magic_quotes_gpc Off
#        php_flag track_vars On
#        php_flag register_globals Off
#    </IfModule>
#
#</Directory>
#EOD
#		ln -s /etc/php5/mods-available/xhprof.ini /etc/php5/apache2/conf.d/20-xhprof.ini
#		ln -s /etc/php5/mods-available/xhprof.ini /etc/php5/cli/conf.d/20-xhprof.ini
#		ln -s /etc/apache2/conf-available/xhprof.conf /etc/apache2/conf-enabled/xhprof.conf
#	fi
#fi

# Install the phpunit libraries.
for i in \
	pdepend/PHP_Depend-beta \
	phpmd/PHP_PMD \
	pear.php.net/Text_Highlighter-0.7.3 \
	PHP_CodeSniffer-1.5.0RC1;
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
# @todo Make this logic a function, (or a part of the download utility), so that I don't have to repeat code here.
safemkdir "/opt/php"

download http://phpdoc.org/phpDocumentor.phar /opt/php/phpDocumentor.phar --smart-overwrite
download https://phar.phpunit.de/phpunit.phar /opt/php/phpunit.phar --smart-overwrite
download https://phar.phpunit.de/phploc.phar /opt/php/phploc.phar --smart-overwrite
download https://phar.phpunit.de/phpcpd.phar /opt/php/phpcpd.phar --smart-overwrite


# Make these all executable!
chmod a+x /opt/php/phpDocumentor.phar
chmod a+x /opt/php/phpunit.phar
chmod a+x /opt/php/phploc.phar
chmod a+x /opt/php/phpcpd.phar

printheader "Installing GEM packages"
gem install sass


if [ "$OSFAMILY" == "debian" -a -n "$(egrep '^[ ]*php_admin_flag' /etc/apache2/mods-enabled/php5.conf)" ]; then
	printwarn "PHP rendering in ~user public_html is disabled!"
	echo "Do you want to enable PHP in public_html? (useful for local development) (y/N)"
	read TRASH
	if [ "$TRASH" == "y" -o "$TRASH" == "Y" ]; then
		# Enable PHP in user directories
		cat /etc/apache2/mods-enabled/php5.conf | sed 's:^[ ]*php_admin_flag:#       php_admin_flag:' > /tmp/installer.php5.tmp
		mv /tmp/installer.php5.tmp /etc/apache2/mods-enabled/php5.conf

		# Enable AllowOverride All, (this is required by Core).
		cat /etc/apache2/mods-enabled/userdir.conf | sed 's:AllowOverride.*:AllowOverride All:' > /tmp/installer.userdir.tmp
		mv /tmp/installer.userdir.tmp /etc/apache2/mods-enabled/userdir.conf

		a2enmod userdir
		printsuccess "Enabled PHP in public_html"
	fi
fi

systemctl restart apache2