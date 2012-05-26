{if $sitecount == 0}
	<p class="message-error">
		There are no update repositories currently enabled.  Go {a href='updater/repos'}Manage Them{/a}!
	</p>
{/if}

{if $sitecount == 1}
	<p>
		There is {$sitecount} update repository currently enabled.  {a href='updater/repos'}Manage Them{/a}
	</p>

	<p>
		{a href='Updater/Check'}Check for updates{/a}
	</p>
{/if}

{if $sitecount > 1}
	<p>
		There are {$sitecount} update repositories currently enabled.  {a href='updater/repos'}Manage Them{/a}
	</p>

	<p>
		{a href='Updater/Check'}Check for updates{/a}
	</p>
{/if}
