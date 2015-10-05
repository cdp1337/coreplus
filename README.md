# About

Core Plus is a PHP-based web framework written to be fast, secure and scalable.  Ultimately, I created this framework
because there were certain quirks with other frameworks and systems that I was not completely satisfied with.

For more info, check out the [Core Plus project site](http://corepl.us)


# Requirements

* PHP 5.4.6 or newer (PHP 5.5.0 and higher is recommended)
* MySQL or MariaDB
* Mod Rewrite, (or comparable module)
* php5-xml
* php5-mcrypt
* php5-curl
* Linux or BSD environment (Tested with Ubuntu 14.04, 14.10, 15.04, Debian 7, CentOS 6.6, and CentOS 7.0)
* memory_limit of 256MB or higher, (installation can be a bit heavy)


# Installation

The main web application of Core Plus is located inside the "src/" directory of the git checkout.
This is the directory that can be exported to production servers.

To install, simply browse to the /install directory via a web browser and follow the instructions.

*NOTE* If you get a bunch of warnings when first loading the page before the framework is installed,
feel free to ignore them and proceed straight to the installer at `/install`.
This is normal to see if `DEVELOPMENT_MODE` is enabled and/or errors are displaying.  It's a side effect of the framework
trying to execute without being initialized first.

@todo If you have recommendations or a patch on how to get around this, feel free to submit them for review!
I'm open to code critique :)


# Known Quirks

## Shared Hosting Environments

Core is not designed to run inside a shared hosting environment, but it can none-the-less.
To get the best performance, a dedicated environment with APC and Memcache running is recommended.

## Windows / WAMP Environment

Installation and running inside a WAMP environment is unsupported at present and may not work.  
If you need this, proceed at your own risk. 

## CentOS 7 (SELinux Permissions)

CentOS 7 ships with SELinux in enforcing mode.  It is always best to leave security features running whenever possible.

Use the following commands if you are having issues with Apache being able to write to the appropriate directories.

	# This will set the context for apache to execute code in the application codebase
	chcon -Rv --type=httpd_sys_content_t /path/to/src
	
	# This will allow apache to write files in the files directory for public and tmp.
	chcon -Rv --type=httpd_sys_script_rw_t /path/to/src/files/

Then, ensure that TMP_DIR in config/configuration.xml points to a directory within the files directory, eg: "files/tmp/"
Sometimes SELinux prevents apache from writing to /tmp, as it's outside the security context for that policy.

## MariaDB Backend

When running with MariaDB, the php5-mysqlnd driver is required instead of php5-mysql!  To fix this on Ubuntu 15.04 and
newer, run `sudo apt-get install php5-mysqlnd`.  This fixes the "Headers and client library minor version mismatch" notice.

## Ubuntu 13.10 Saucy Salamander

Installation requires PHP's mcrypt library, but in Ubuntu 13.10 Saucy Salamander, that library is a little broken.
To install Core on 13.10, you first need to move the `.ini` file from `/etc/php5/conf.d` to `/etc/php5/mods-available`,
and then issue `sudo php5enmod mcrypt` followed by `sudo service apache2 restart`.

## Installations with no Assets

Occasionally there are no assets for new installations.
If this happens, switch DEVELOPMENT_MODE to true in your config/configuration.xml and refresh the page.


# Support

Core Plus is provided as-is and is not guaranteed not to kill your cat and eat your first born.
Feel free to submit bug tickets and feature requests on [Github's bug tracker](https://github.com/nicholasryan/CorePlus/issues).

If you need enterprise support for the framework, (and any other custom development requests), 
[eVAL Agency](https://eval.agency/services/business-communications/core-plus-licensing)
is the official service partner for Core Plus.

# Licenses

The core framework is licensed under the AGPLv3.  Included libraries are a variety of other open licenses, such as
Apache License, MIT, LGPL, etc.


# Bundled Libraries &amp; Utilities

* [AWS SDK](http://aws.amazon.com/sdkforphp/)
  * http://aws.amazon.com/sdkforphp/
  * Licensed under Apache
* [phpass](http://www.openwall.com/phpass/)
  * http://www.openwall.com/phpass/
  * Licensed under MIT
* phpmailer (Modified)
  * Original: http://code.google.com/a/apache-extras.org/p/phpmailer/
  * Licensed under LGPL
* Smarty 3 (Modified)
  * Original: http://www.smarty.net
  * Licensed under LGPL
* Pear/SQL_Parser
* Bacon Ipsum (Completely rewritten)
* perl/xpath
* Yahoo User Interface Compressor
* SASS
* jQuery &amp; jQueryUI
* Font-Awesome
* MaxMind Geo-Location