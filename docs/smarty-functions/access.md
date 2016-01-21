# {access} Smarty Block

A smarty block to limit the inner contents by a user access string check.

## Parameters

* Access string
* (optional) "!" to inverse the result

## Example Usage

	{access "g:admin"}
	<p>This snippet is only visible to administrators.</p>
	{/access}

	{access '!' 'g:admin'}
	<p>While this snippet is visible to everyone except admins.</p>
	{/access}

	{access 'p:/something/blah'}
	<p>Content only for people with the /something/blah permission.</p>
	{/access}