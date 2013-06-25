# Ant Properties Example Template
#
# Use this file as a template for your own "ant.properties" file for building the application with ant.
#
# (${basedir} is an automatic variable set by the ant system.)
# It's default to the directory the build.xml file is located in.


# Rewrite base for the site.
#
# If this is currently installed in /~userblah/coreplus, set this to
# /~userblah/coreplus/
#
# This gets copied to the .htaccess file.
rewritebase=/src/


# Database server name or IP address
# Gets copied to the config/configuration.xml file.
db.server=localhost

# Database port
# Gets copied to the config/configuration.xml file.
db.port=3306


# Database type
# Gets copied to the config/configuration.xml file.
db.type=mysqli


# Database name
# Gets copied to the config/configuration.xml file.
db.name=coreplus


# Database user
# Gets copied to the config/configuration.xml file.
db.user=coreplus


# Database password
# Gets copied to the config/configuration.xml file.
db.pass=coreplus


# Controls if the site is in DEVELOPMENT mode or not.
#
# Setting this to "false" will be equivalent to production and "true" will be development.
#
# Gets copied to the config/configuration.xml file.
devmode=false


# Upstream directory
#
# If you have Core Plus checked out locally, enter the full path here.
# This is used by the sync_from_upstream.sh script for importing changes in Core and the core components.
upstream=
