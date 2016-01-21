# {css} Smarty Block

The recommended way to inject stylesheets into your application.

Any inline styles or links to stylesheets added via the `{css}` smarty block are automatically moved into the head of the document.
Redundant file includes and inline styles are omitted automatically.

CSS files have their minified version sent automatically when the Core config option is set to do so.

## Parameters

* media
	* (string) The media attribute, defaults to "all".
* href
	* The source of the linked CSS asset.
	* Can be fully resolved or a Core asset/* path.
* link
	* alias of href.
* src
	* alias of href.
* optional
	* Set to "1" if this is an optional stylesheet where the admin can toggle on/off its inclusion.
	* Currently only supported in theme skins.
* default
	* If optional="1", this is if the file is included by default or not.
* title
	* If optional="1", this is an optional title displayed for the admin.

## Example Usage

	{css src="css/styles.css"}{/css}
	{css src="css/opt/full-width.css" optional="1" default="0" title="Set the page to be full width"}{/css}