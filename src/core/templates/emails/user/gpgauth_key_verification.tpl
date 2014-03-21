<p>
	A recent request to set or reset your GPG key for logging in has been made.
	If you did not perform this request, then be aware that someone may be trying to do something malicious!
</p>

<strong>Request Details:</strong>
<ul>
	<li>New Key: {$key}</li>
	<li>IP: {$smarty.const.REMOTE_IP}</li>
	<li>City: {$smarty.const.REMOTE_CITY}</li>
	<li>State: {$smarty.const.REMOTE_PROVINCE}</li>
</ul>

<p>
	If you did request this change, then execute the following command in a terminal window and paste in the results in the textarea.
</p>

<pre>
	echo -n "{$sentence}" | gpg -b -a
</pre>
