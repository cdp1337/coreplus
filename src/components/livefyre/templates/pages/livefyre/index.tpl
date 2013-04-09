{img src="assets/images/livefyre/logo-header.png"}

{if !$siteid}
	<p class="message-info">
		Please setup a Livefyre account and register this site to obtain a site id.

	</p>

	<form action="http://www.livefyre.com/install/postauth/" target="_blank" method="POST">
		<fieldset>
			<legend> New to LiveFyre? </legend>
			<a class="button" href="http://www.livefyre.com/auth/register/?next=/install" target="_blank">
				{img src="assets/images/livefyre/livefyre-16x16.png"}
				Create a LiveFyre account!
			</a>
		</fieldset>

		<fieldset>
			<legend> Already have LiveFyre? </legend>
			<input type="hidden" name="keep-going" value="Keep Going!"/>
			<input type="hidden" name="platform" value="custom"/>
			<input type="hidden" name="url" value="{$url}"/>
			<input type="submit" value="Just grab the site id"/>
		</fieldset>
	</form>

	<br/>
	(<a href="http://www.livefyre.com/install/" target="_blank">LiveFyre's Installation Page</a>)

	<p class="message-tutorial">
		Once you have an account and have regsitered this site, enter the "Site ID" under "Developer Info"
		to the right of the LiveFyre installation page.
	</p>

	{$form->render('head')}
	{$form->render('body')}
	<input type="submit" value="Set Site ID"/>
	{$form->render('foot')}

	<!--
keep-going	Keep Going!
platform	custom
url	http://corepl.us
	-->
{else}

	<p class="message-tutorial">
		If you've forgotten, the site id can be acquired from the
		<a href="http://www.livefyre.com/install/" target="_blank">LiveFyre installation page</a>
		under "Developer Info".
	</p>

	{$form->render('head')}
	{$form->render('body')}
	<input type="submit" value="Update Site ID"/>
	{$form->render('foot')}

{/if}