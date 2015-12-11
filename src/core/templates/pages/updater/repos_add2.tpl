<p class="message-info">Confirmation of repository {$url}</p>

<h3>Description: </h3>
<p class="repo-description">
	{$description}
</p>

<h3>Embedded Keys: </h3>
<div class="repo-keys">
	{if sizeof($keys)}
		<p>The following keys will be installed automatically!</p>
		<ul>
			{foreach $keys as $key}
				<li>
					<p>
						{$key.fingerprint}
						{foreach $key.uids as $u}
							<br/>{$u.name} &lt;{$u.email}&gt;
						{/foreach}
					</p>
				</li>
			{/foreach}
		</ul>
	{else}
		<p>There are no keys embedded with this repository.</p>
	{/if}
</div>

<br/>
<p>
	Look over the above information closely.  If you do not trust something above, DO NOT PROCEED!
</p>

<form action="" method="POST">
	<input type="hidden" name="model[url]" value="{$url}"/>
	<input type="hidden" name="model[username]" value="{$username}"/>
	<input type="hidden" name="model[password]" value="{$password|escape}"/>
	<input type="hidden" name="confirm" value="1"/>
	<input type="submit" value="Confirm and Import"/>
</form>