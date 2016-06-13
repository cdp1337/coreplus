{if $error}
	<p class="message-error">{$error}</p>
{/if}

<p class="message-tutorial">
	{a href="http://www.gnupg.org/" target="_BLANK"}GPG Keys <i class="icon icon-external-link"></i>{/a} are used extensively
	in Core to ensure that the contents of a package are exactly as the publisher expects.  This is done by the publisher
	signing their package with their private key at the time of creation.  That signature can be used to cryptographically
	verify that the file is byte for byte verbatim what it is expected to be.  This ensures that what is being installed
	through the updater has not been tampered with.
</p>

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