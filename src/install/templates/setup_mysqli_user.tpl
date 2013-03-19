<h2>MySQL/MySQLi User Access</h2>

<p class="message-note">
	You currently have the "type" variable in 
	config/db.xml set to "mysql" or "mysqli".  This will use the 
	<strike>MySQL</strike> <strike>Sun</strike> Oracle MySQL backend storage engine 
	for the default site datamodel store.  If this is incorrect, please correct this 
	<em>before</em> proceeding.  Otherwise... please verify that the settings in 
	config/configuration.xml are as desired and continue.
</p>

<p>
	Seems as you have either an incorrect password or the user does not exist.  If you wish to create the mysql user, please execute the following commands with mysql or another interface, (like phpMyAdmin or toad).
</p>
<pre>
CREATE USER '%dbuser%' IDENTIFIED BY '%dbpass%';
FLUSH PRIVILEGES;
</pre>

<p class="message-note">IF... doing the above still results in an access denied for user error, remove your anonymous localhost user!  Alternatively, just change the USER directive to '%dbuser%'@'localhost'</p>


<p>Refresh the page when this has been done.</p>
