# Development Files Readme

The `dev` directory of this component is useful for any file or asset that is
not intended to be packaged in the final build.
This is generally used because the enclosed files are applicable to the
build team and developers only.

## Common Files

Some common uses of this directory are to contain

* Source image files, (XCF, PNG, etc)
* Supplemental developer-only documentation
* Random files useful for developers only
* SASS/SCSS source files

_You get the idea here._

## SASS Assets

One special use of this directory is for SASS/SCSS assets.
Any `*.scss` or `*.sass` file located in `dev/assets/scss/*`
will get compiled and minified to `assets/css/*`.