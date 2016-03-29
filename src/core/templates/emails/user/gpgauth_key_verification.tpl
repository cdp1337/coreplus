<h1>GPG Change on {$smarty.const.SITENAME}!</h1>

<p>
	A recent request from <strong>{$smarty.const.REMOTE_CITY}, {$smarty.const.REMOTE_PROVINCE} ({$smarty.const.REMOTE_IP})</strong>
	to set your GPG key to <strong>{$key|gpg_fingerprint}</strong>
	has been made.  If this action was performed by you, please confirm the key by running the following command in a terminal window.
	to set or reset your GPG key for logging in has been made.
</p>

<pre style="font-family:monospace; font-size:1em;"><code class="bash">{$cmd}</code></pre>

<hr/>

<p>
	If you did not make this request, then be aware that someone may be trying to do something malicious!
</p>

<hr/>

<strong>Request Details:</strong>
<ul>
	<li>New Key: {$key}</li>
	<li>IP: {$smarty.const.REMOTE_IP}</li>
	<li>City: </li>
	<li>State: </li>
</ul>
