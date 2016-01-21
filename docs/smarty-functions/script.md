# {script} Smarty Block

{script} is the main way to load javascript into Core from a template.

This block can be used in various ways, as illustrated in the examples.

## Parameters

* name
	* string, the name of the registered script library to include
	* ex: "jquery", "jqueryui", etc.
* src
	* string, full resolved URL or Core-resolvable location of the asset.
* location
	* string, "foot", or "head".  Foot will append the script block at the end of the body, head inside the &lt;head/&gt; tag.

## Example Usage

	// Include jquery on this page.
	{script name="jquery"}{/script}

	// Another way to call javascript libraries
	{script library="jquery"}{/script}

	// Traditional "src" tags work too
	{script src="http://blah.tld/js/jquery.js"}{/script}

	// Because it's a block-type tag... you can also do
	{script}
	// This section is automatically plugged into a <script> tag.
	{/script}

	// Specifying the location of the target rendering area is also allowable.
	// This is useful for scripts that expect to be at the end of the body tag.
	{script src="js/mylib/foo.js" location="head"}{/script}
	{script src="js/mylib/foo.js" location="foot"}{/script}

