<p class="message-tutorial">
	A simple site password provides a mechanism for simple site-wide authentication.
	This is useful for site in development or staging, where bots and crawlers are needed to be kept out,
	but no major authentication is required.
	<br/><br/>
	Only one password is supported, and this password is not encrypted in anyway, so a secure one should <b>not</b> be used.
	<br/><br/>
	Please note, this site-password is not compatible with Apache's .htpasswd system, but both provide similar functionality.
</p>
{$form->render()}