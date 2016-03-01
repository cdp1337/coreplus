# {asset} Smarty Function

The {asset} function in smarty can resolve the link to an asset in assets/.
This can be an image, CSS file, javascript file, or any other static asset defined in a component or theme.

## Parameters

Required parameter is one unnamed parameter which is the base path of the asset.
Additionally this can be called "file", "src", or "href".

Optional parameters include:

* width
* height
* dimensions

Any of the width/height/dimensions parameters set the dimensions of an image if an image is requested.
It has no function if a CSS, JS, or other text resource is returned.