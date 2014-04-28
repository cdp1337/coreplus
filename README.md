# About

Core Plus is a PHP-based web framework written to be fast, secure and scalable.  Ultimately, I created this framework
because there were certain quirks with other frameworks and systems that I was not completely satisfied with.

For more info, checkout the [Core Plus project site](http://corepl.us)


# Requirements

* PHP 5.4.0 or newer
* MySQL (currently the only supported database)
* Mod Rewrite, (or comparable module)
* php-xml
* php-mcrypt
* php-curl
* Linux or BSD environment


# Installation

The main web application of Core Plus is located inside the "src/" directory of the git checkout.
This is the directory that can be exported to production servers.

To install, simply browse to the /install directory via a web browser and follow the instructions.


# Known Quirks

## Ubuntu 13.10 Saucy Salamander

Installation requires PHP's mcrypt library, but in Ubuntu 13.10 Saucy Salamander, that library is a little broken.
To install Core on 13.10, you first need to move the `.ini` file from `/etc/php5/conf.d` to `/etc/php5/mods-available`,
and then issue `sudo php5enmod mcrypt` followed by `sudo service apache2 restart`.

## Installations with no Assets

Occasionally there are no assets for new installations.
If this happens, switch DEVELOPMENT_MODE to true in your config/configuration.xml and refresh the page.


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