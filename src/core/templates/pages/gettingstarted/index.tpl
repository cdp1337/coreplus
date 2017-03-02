<p class="message-success">
	{t 'MESSAGE_CORE_PLUS_SUCCESSFULLY_INSTALLED'}
</p>

{if $isadmin}
	<p>
		A possible next step is to create a page with the url of "/" to act as the homepage.
		Doing so will automatically replace this getting started page.
		Any component that supports custom URLs can be used, be it a blog, content or splash page.

		Head over to {a href="/admin"}the administration landing page{/a} to get started.
	</p>
{/if}

{if $rewrite_not_available}
	<p class="message-tutorial">
		Rewrite URLs do not appear to be working!  This is usually caused by an "AllowOverride None"
		directive in a system file.
		
		{if $rewrite_config}
			<br/><br/>Try checking in {$rewrite_config} for that directive and change it to "AllowOverride All"
			followed by a restart command to Apache.
		{/if}
	</p>
{elseif $showusercreate}
	<p class="message-info">Please create the administrative user account.  This first account will be granted with full access to the site.</p>
	{widget baseurl="user/register"}
{else}
	{widget baseurl="userlogin/execute"}
{/if}

