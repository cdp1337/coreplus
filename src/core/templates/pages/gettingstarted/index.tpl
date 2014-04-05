<p class="message-success">
	Core Plus framework has been successfully installed!
</p>

{if $isadmin}
	<p>
		A possible next step is to create a page with the url of "/" to act as the homepage.
		Doing so will automatically replace this getting started page.
		Any component that supports custom URLs can be used, be it a blog, content or splash page.

		Head over to {a href="/admin"}the administration landing page{/a} to get started.
	</p>
{/if}

{if $showusercreate}
	<p class="message-info">Please create the administrative user account.  This first account will be granted with full access to the site.</p>
	{widget baseurl="user/register"}
{else}
	{widget baseurl="userlogin/execute"}
{/if}

