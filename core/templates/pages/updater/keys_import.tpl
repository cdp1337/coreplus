{if $error}
	<p class="message-error">{$error}</p>
{/if}

<form action="{link link='/updater/keys/import'}" method="POST" enctype="multipart/form-data">
	Add a public key via ID<br/>
	<input type="text" name="pubkeyid"/>
	<input type="submit" value="Lookup and Add Key"/>

	<br/><hr/>OR<hr/>

	Copy in the public key<br/>
	<textarea cols="65" rows="20" name="pubkey"></textarea>
	<br/>
	<input type="submit" value="Import Key"/>

	<br/><hr/>OR<hr/>

	Or upload a .pub file
	<input type="file" name="pubkeyfile"/>
	<br/>
	<input type="submit" value="Import Key File"/>
</form>