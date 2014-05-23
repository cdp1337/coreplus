# {a} Smarty Block

This is the recommended way to handle <a/> tags in Core templates.
The href attribute is automatically resolved to the primary rewrite URL.

## Parameters

* confirm
	* Set to a string to prompt the user with the string before submitting the link via a POST request.

* history
	* Set to a number, (1, 2, etc), to set the href to that user's last nth page from history.

## Example Usage

	{a href="/content/view/1"}Something Blah{/a}
	// Resolves to
	<a href="/homepage">Something Blah</a>