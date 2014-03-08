#!/bin/bash
# Script to import data from a production database to local.
#


# Core Plus base directory
BASEDIR="$(readlink -f $(dirname $0)/..)"
# The root directory, relative to the script
HERE="$(dirname $0)"

echo "Loading basescript..."
source "/opt/eval/basescript.sh"


# Production settings.
HOST="$(egrep '^data\.import\.production\.host=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
USER="$(egrep '^data\.import\.production\.user=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
PORT="$(egrep '^data\.import\.production\.port=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
DIR="$(egrep '^data\.import\.production\.datadir=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
DEVDATA="$(egrep '^data\.import\.custom\.datafile=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"

# Local settings.
DBHOST="$(egrep '^db\.server=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
DBUSER="$(egrep '^db\.user=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
DBPASS="$(egrep '^db\.pass=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
DBNAME="$(egrep '^db\.name=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"
#DBPORT="$(egrep '^db\.port=' "$BASEDIR/ant.properties" | sed 's:^[^=]*=\(.*\):\1:')"


if [ -e "$BASEDIR/data/data-latest.sql.gz" -a "$HOST" == "" ]; then
	printheader "Production host not set but latest data available, importing from cached datafile."
	DOWNLOADIT=0
elif [ -a "$HOST" ]; then
	printerror "Production host not set and no cached datafile available."
	exit 1
else
	# Get the newest version from the remote server.
	#REMOTESQLGZ="$(ssh $HOST \"ls -c $HOSTSRC/*.sql.gz \| head -n1\")"
	#REMOTESQLGZ="$(ssh $HOST \'ls -c \"$HOSTSRC/*.sql.gz\"\')"
	REMOTESQLGZ="$(ssh -p$PORT $USER@$HOST ls -c $DIR*.sql.gz \| head -n1)"
	if [ -z "$REMOTESQLGZ" ]; then
		echo "No *.sql.gz file located within $DIR on host $HOST."
		exit 1
	fi

	if [ -e "$BASEDIR/data/data-latest.sql.gz" ]; then
		# Check and see if I need to re-download this file.
		REMOTEMD5="$(ssh -p$PORT $USER@$HOST md5sum $REMOTESQLGZ \| sed \'s: .*$::\')"
		LOCALMD5="$(md5sum "$BASEDIR/data/data-latest.sql.gz" | sed 's: .*$::')"
		if [ "$REMOTEMD5" == "$LOCALMD5" ]; then
			DOWNLOADIT=0
		else
			DOWNLOADIT=1
		fi
	else
		DOWNLOADIT=1
	fi
fi


if [ "$DOWNLOADIT" == "1" ]; then
	# Get that file and download it to "newest"
	download ssh://$HOST:$REMOTESQLGZ "$BASEDIR/data/data-latest.sql.gz" -P $PORT -u $USER -o
	#scp $HOST:$REMOTESQLGZ $HERE/data-newest.sql.gz
	if [ "$?" != "0" ]; then
		echo "something bad happened"
		exit 1
	fi
fi

printheader "Importing database..."
gunzip "$BASEDIR/data/data-latest.sql.gz" -c | mysql -u$DBUSER -p"$DBPASS" $DBNAME
checkexitstatus "$?"

# Run any upgrades queued up for the "next" version of production.
if [ -e "$BASEDIR/data/prod-next-upgrade.sql" ]; then
	printheader "Applying production upgrade patch..."
	mysql -u$DBUSER -p"$DBPASS" $DBNAME < "$BASEDIR/data/prod-next-upgrade.sql"
	checkexitstatus "$?"
fi

# Run any production-to-development data.
if [ -e "$BASEDIR/data/prod-to-dev.sql" ]; then
	printheader "Applying production-to-development patch..."
	mysql -u$DBUSER -p"$DBPASS" $DBNAME < "$BASEDIR/data/prod-to-dev.sql"
	checkexitstatus "$?"
fi

# Run any developer-specific file specified in the ant.properties.
if [ -n "$DEVDATA" -a -e "$BASEDIR/data/$DEVDATA" ]; then
	printheader "Applying developer-specific $DEVDATA patch..."
	mysql -u$DBUSER -p"$DBPASS" $DBNAME < "$BASEDIR/data/$DEVDATA"
	checkexitstatus "$?"
fi
