<p class="message-tutorial">
	Run the following command with GPG and your private key and paste in the results.
	This will verify that you have access to the private key.
	<br/><br/>
	You have approximately 5 minutes before this login expires.
</p>

<pre class="code">
	echo -n "{$sentence}" | gpg -b -a
</pre>

{$form->render()}