<h2>Cassandra Installation Instructions</h2>

<p class="message-note">
	You currently have the "type" variable in 
	config/db.xml set to "cassandra".  This will use the Apache Cassandra data 
	storage engine for the default site datamodel store.  If this is incorrect, please
	correct this <em>before</em> proceeding.  Otherwise... please verify that the 
	settings in config/core.xml and config/db.xml are as desired and continue.
</p>

<p>
	Please execute the following commands with cassandra-cli or another interface.
</p>

<pre>create keyspace %dbname%;</pre>

<p>Refresh the page when this has been done.</p>
