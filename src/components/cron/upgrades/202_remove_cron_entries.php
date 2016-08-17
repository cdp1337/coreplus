<?php
/**
 * Upgrade script to remove /cron requests from the user activity log.
 * 
 * These are system level checks that do not need to be recorded.
 */

\Core\Datamodel\Dataset::Init()
	->delete()
	->table('user_activity')
	->where('baseurl = /cron')
	->execute();

\Core\Datamodel\Dataset::Init()
	->delete()
	->table('user_activity')
	->where('baseurl = /cron/monthly')
	->execute();

\Core\Datamodel\Dataset::Init()
	->delete()
	->table('user_activity')
	->where('baseurl = /cron/weekly')
	->execute();

\Core\Datamodel\Dataset::Init()
	->delete()
	->table('user_activity')
	->where('baseurl = /cron/hourly')
	->execute();

\Core\Datamodel\Dataset::Init()
	->delete()
	->table('user_activity')
	->where('baseurl = /cron/daily')
	->execute();