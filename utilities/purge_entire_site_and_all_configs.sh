#!/bin/bash
#
# This script will, just like the name says, go this application and purge everything and
# restore it back to git checkout defaults.
# aka, DO NOT USE THIS!

ROOTPDIR="$(realpath $(dirname $0)/../src)"

REALLY=""
REALLYREALLY=""

for i in $@; do
	case "$i" in
		"--really" )
			REALLY="1"
			;;
		"--reallyreally" )
			REALLYREALLY="1"
			;;
	esac
done

if [ -z "$REALLY" -o -z "$REALLYREALLY" ]; then
	echo "No really really?  Ok, aborting!"
	exit 1
fi


if [ ! -e "$ROOTPDIR/config/configuration.xml" ]; then
	echo "No configuration file located, is this site installed?"
	exit 1
fi


# Before I purge the configuration file... I need to pull out the relevant information.
DBHOST="$(xpath -q -e '/configuration/return[@name="database_server"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
DBPORT="$(xpath -q -e '/configuration/return[@name="database_port"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
DBTYPE="$(xpath -q -e '/configuration/return[@name="database_type"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
DBNAME="$(xpath -q -e '/configuration/return[@name="database_name"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
DBUSER="$(xpath -q -e '/configuration/return[@name="database_user"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
DBPASS="$(xpath -q -e '/configuration/return[@name="database_pass"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";

SITENAME="$(xpath -q -e '/configuration/define[@name="SITENAME"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
CDNTYPE="$(xpath -q -e '/configuration/define[@name="CDN_TYPE"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
CDNLOCALASSETDIR="$(xpath -q -e '/configuration/define[@name="CDN_LOCAL_ASSETDIR"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";
CDNLOCALPUBLICDIR="$(xpath -q -e '/configuration/define[@name="CDN_LOCAL_PUBLICDIR"]/value' src/config/configuration.xml | sed 's:^<[^>]*>::' | sed 's:</[^>]*>$::')";


echo "Beginning purge of site $SITENAME..."


case "$DBTYPE" in
	"mysql" | "mysqli" )
		for table in $(mysql -h"$DBHOST" -P"$DBPORT" -u"$DBUSER" -p"$DBPASS" $DBNAME --batch --raw --skip-column-names -e "SHOW TABLES;"); do
			echo "Dropping table $table..."
			mysql -h"$DBHOST" -P"$DBPORT" -u"$DBUSER" -p"$DBPASS" $DBNAME --batch --raw --skip-column-names -e "DROP TABLE $table";
		done
		;;
esac


# Now I can remove the config and dynamic files!
rm -fr "$ROOTPDIR/config/configuration.xml"
rm -fr "$ROOTPDIR/.htaccess"

# And the CDN files.
case "$CDNTYPE" in
	"local" )
		if [ ! -z "$CDNLOCALASSETDIR" ]; then rm -fr "$ROOTPDIR/$CDNLOCALASSETDIR"; fi
		if [ ! -z "$CDNLOCALPUBLICDIR" ]; then rm -fr "$ROOTPDIR/$CDNLOCALPUBLICDIR"; fi
		;;
esac
