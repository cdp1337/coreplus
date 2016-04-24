{if $server_id && strlen($server_id) == 39}
	<p class="message-info">
		{t 'MESSAGE_INFO_SERVER_KEY_IS_S' $server_id}
	</p>
{else}
	<p class="message-tutorial">
		{t 'MESSAGE_TUTORIAL_SERVER_KEY_INSTALL_TO_CONFIG'}
	</p>
	
	<pre>
&lt;define name="SERVER_ID" type="string" formtype="text" advanced="1"&gt;
	&lt;value&gt;{$new_key}&lt;/value&gt;
	&lt;description&gt;
		The server ID when used in a multi-server environment and 
		the full global ID for this server when used with licensed software.
	&lt;/description&gt;
&lt;/define&gt;

</pre>
{/if}
