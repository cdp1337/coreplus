#!/bin/bash
#
# This script will download everything necessary for running Core Plus in production.
#

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
	if [ "$OSVERSIONMAJ" -ge 16 ]; then
		install php7.0-xml \
			php7.0-mysql \
			php7.0-mcrypt \
			php7.0-curl \
			php7.0-gd \
			php7.0-bcmath \
			php7.0-mbstring \
		    libapache2-mod-php7.0 \
			mariadb-client-10.0 \
			mariadb-server-10.0
	else
		# Ubuntu 15.04 requires the nd library for phpmysql.
		install php5-xml \
			php5-mysqlnd \
			php5-mcrypt \
			php5-curl \
			php5-gd \
			php5-mbstring \
			php5-bcmath
		a2enmod rewrite
	fi
elif [ "$OSFAMILY" == "debian" ]; then
	if [ "$OSVERSIONMAJ" -ge 9 ]; then
		# Use the native driver in stretch and above.
		# Also install PHP 7.x instead of 5.x by default!
		install php7.0-xml \
			php7.0-mysql \
			php7.0-mcrypt \
			php7.0-curl \
			php7.0-gd \
			php7.0-bcmath \
			php7.0-mbstring \
		    libapache2-mod-php7.0 \
			mariadb-client-10.0 \
			mariadb-server-10.0
		a2enmod rewrite
		a2enmod php7.0
	else
		# Debian 8 (Jessie) and lower.
		install apt-transport-https
		wget https://www.dotdeb.org/dotdeb.gpg -S -O - | apt-key add -
		echo 'deb https://packages.dotdeb.org jessie all' > /etc/apt/sources.list.d/dotdeb.list
		echo 'deb-src https://packages.dotdeb.org jessie all' >> /etc/apt/sources.list.d/dotdeb.list
		install php7.0-xml \
			php7.0-mysql \
			php7.0-mcrypt \
			php7.0-curl \
			php7.0-gd \
			php7.0-bcmath \
			php7.0-mbstring \
		    libapache2-mod-php7.0 \
			mariadb-client-10.0 \
			mariadb-server-10.0
		a2enmod rewrite
		a2enmod php7.0
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
	echo "We'd love your contribution for this operating system!"
	echo "Post to https://rm.eval.bz/projects/coreplus your howto and help out the project."
	exit 1
fi


if [ "$OSFAMILY" == "debian" -a -n "$(egrep '^[ ]*php_admin_flag' /etc/apache2/mods-enabled/php7.0.conf)" ]; then
	printwarn "PHP rendering in ~user public_html is disabled!"
	echo "Do you want to enable PHP in public_html? (useful for local development) (y/N)"
	read TRASH
	if [ "$TRASH" == "y" -o "$TRASH" == "Y" ]; then
		# Enable PHP in user directories
		cat /etc/apache2/mods-enabled/php7.0.conf | sed 's:^[ ]*php_admin_flag:#       php_admin_flag:' > /tmp/installer.php7.tmp
		mv /tmp/installer.php7.tmp /etc/apache2/mods-enabled/php7.0.conf

		# Enable AllowOverride All, (this is required by Core).
		cat /etc/apache2/mods-enabled/userdir.conf | sed 's:AllowOverride.*:AllowOverride All:' > /tmp/installer.userdir.tmp
		mv /tmp/installer.userdir.tmp /etc/apache2/mods-enabled/userdir.conf

		a2enmod userdir
		printsuccess "Enabled PHP in public_html"
	fi
fi

svc apache2 restart