# {img} smarty function

The {img} smarty function is the recommended way to load images in templates from asset or public directories.  In addition to automatically resolving URLs, it can also handle server-side resizing and a few other nifty features.

## Parameters

* file
    * File_Backend
    * File object passed in to display
    * Either "file" or "src" is required.

* src
    * string
    * Source filename to display.  This can start with "assets" for an asset, or "public" for a public file.
    * Either "file" or "src" is required.

* width
    * int
    * Maximum image width (in pixels).  If both width and height are provided, the image will be constrained to both without any distortion.

* height
    * int
    * Maximum image height (in pixels).  If both width and height are provided, the image will be constrained to both without any distortion.

* placeholder
    * string
    * placeholder image if the requested image is blank or not found.  Useful for optional fields that should still display something.
    * Current possibilities: "generic", "person", "person-tall", "person-wide", "photo"

Any other parameter is transparently sent to the resulting `<img/>` tag.


## Examples

    {img src="public/gallery/photo123.png" width="123" height="123" placeholder="photo" alt="My photo 123"}