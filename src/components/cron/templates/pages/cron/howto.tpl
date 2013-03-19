<p>
	To install a cron job on your site, you need to use the Linux crontab system or other resource.
	This will ensure that crons are executed exactly on time every time.
</p>
<p>
	For the weekly and monthly jobs, feel free to schedule them to run whenever you prefer for the site.
	As long as they are executed a week and a month apart, the scripts that are called won't mind a bit.
	There are several options provided for a quickstart; use whichever you need.
</p>

<h3>Commands:</h3>

<p>
	This is useful for cpanel and other services,  copy and paste these appropriately into your manager.
</p>

<br/>
<pre>
wget -q {$url}cron/hourly -O /dev/null

wget -q {$url}cron/daily -O /dev/null

wget -q {$url}cron/weekly -O /dev/null

wget -q {$url}cron/monthly -O /dev/null
</pre>

<br/><br/>


<h3>User Crontab Format:</h3>

<p>
	This is useful for user cronjob files.
</p>

<br/>
<pre>
# Execute the hourly webcron for {$sitename}
0  *  *  *  *  wget -q {$url}cron/hourly -O /dev/null

# Execute the daily webcron for {$sitename}
5  0  *  *  *  wget -q {$url}cron/daily -O /dev/null

# Execute the weekly webcron for {$sitename}
10 0  *  *  0  wget -q {$url}cron/weekly -O /dev/null

# Execute the monthly webcron for {$sitename}
20 0  1  *  *  wget -q {$url}cron/monthly -O /dev/null
</pre>

<br/><br/>


<h3>/etc/crontab Format:</h3>

<p>
	This is useful for the /etc/crontab file.
</p>

<br/>
<pre>
# Execute the hourly webcron for {$sitename}
0  *  *  *  *  nobody  wget -q {$url}cron/hourly -O /dev/null

# Execute the daily webcron for {$sitename}
5  0  *  *  *  nobody  wget -q {$url}cron/daily -O /dev/null

# Execute the weekly webcron for {$sitename}
10 0  *  *  0  nobody  wget -q {$url}cron/weekly -O /dev/null

# Execute the monthly webcron for {$sitename}
20 0  1  *  *  nobody  wget -q {$url}cron/monthly -O /dev/null
</pre>