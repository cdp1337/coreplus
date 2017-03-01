# Ant Properties Example Template
#
# Use this file as a template for your own "ant.properties" file for building the application with ant.
#
# (${basedir} is an automatic variable set by the ant system.)
# It's default to the directory the build.xml file is located in.



###############################################################################
####                        BASIC SITE SETTINGS                            ####
###############################################################################

# Rewrite base for the site.
#
# If this is currently installed in /~userblah/coreplus, set this to
# /~userblah/coreplus/
#
# This gets copied to the .htaccess file.
rewritebase=/src/


# Controls if the site is in DEVELOPMENT mode or not.
#
# Setting this to "false" will be equivalent to production and "true" will be development.
#
# Gets copied to the config/configuration.xml file.
devmode=false



###############################################################################
####                      LOCAL DATABASE SETTINGS                          ####
###############################################################################


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




# Upstream directory
#
# If you have Core Plus checked out locally, enter the full path here.
# This is used by the sync_from_upstream.sh script for importing changes in Core and the core components.
upstream=



###############################################################################
####                        DATA IMPORT SETTINGS                           ####
###############################################################################
####
#### All of these settings control how the utilities/import-data.sh script works.
#### If you do not intend to use this script, these settings are optional.
####
#### This is useful for development or demo environments where data should be imported from another source.


# Server IP or hostname used to retrieve the database backup.
data.import.production.host=


# Server username used to retrieve the database backup.
data.import.production.user=


# Server port number for SSH used to retrieve the database backup.
data.import.production.port=22


# Directory on the server that contains the database backup.
# MUST end with a trailing slash.
#
# This directory is searched for the most recent *.sql.gz file.
data.import.production.datadir=/home/example/backups/

# Alternatively, if a specific file is known on the remote server,
# uncomment the following and set to pull from that file.
#data.import.production.datafile=/var/unified_backups/db-local-copies/database.sql.gz


# Any developer-specific operations to perform after import.
# This file must be located inside the data/ directory of the project.
data.import.custom.datafile=

# SonarQube specific properties
# All properties are required.
#sonar.host.url=
#sonar.login=
#sonar.projectKey=
#sonar.projectName=CorePlus
#sonar.projectVersion=1.0
#sonar.sources=src
#sonar.exclusions=src/files/*,src/core/bootstrap.compiled.php