<h2>MySQL/MySQLi Database Setup</h2>

<p class="message-note">
	You currently have the "type" variable in 
	config/db.xml set to "mysql" or "mysqli".  This will use the 
	<strike>MySQL</strike> <strike>Sun</strike> Oracle MySQL backend storage engine 
	for the default site datamodel store.  If this is incorrect, please correct this 
	<em>before</em> proceeding.  Otherwise... please verify that the settings in 
	config/configuration.xml are as desired and continue.
</p>

<p>
	Please execute the following commands with mysql or another interface, (like phpMyAdmin or toad).
</p>

<pre>
CREATE DATABASE IF NOT EXISTS %dbname%;
GRANT ALL ON %dbname%.* TO '%dbuser%';
FLUSH PRIVILEGES;
</pre>

<p>Refresh the page when this has been done.</p>
