# CSS or SCSS?

Which styling system should you use when creating a theme or component, CSS, SCSS, or to a lesser extent even, LESS?

In Core, all three are supported, though each have their advantages.
Regardless of which one you use however, you will need to make sure that the asset is located in the proper directory
 and that your component.xml or theme.xml has the file registered.

## Packager and Meta-Files

Core relies heavily on .xml meta-files to know what files are contained in each theme and component,
asset resources are no exception.
Every asset that is to be copied to the deployed `files/assets/` directory must be registered within the
`<assets>` node of your `theme.xml` or `component.xml` as a `<file/>` directive.

This is done automatically when `utilities/packager.php` is executed and your component/theme is selected to be built.
Optionally, you can call `utilities/packager.php -r -c name-here` to repackage a single component,
or `utilities/packager.php -r -t name-here` to repackage a single theme.

When Core is installed and reinstalled, it scans through every component and theme metafile and looks for these assets.
For each asset, it copies that source file to the system-defined `files/assets/default/` directory or appropriate assets CDN.


## CSS

CSS is the simplest to use, not requiring any special knowledge and minimal compiling to get working.
All CSS files must be located within `assets`, and should be located within `assets/css/some-name/`.
When installed, your component or theme will have the contents of `assets` copied to `files/assets/default/`,
so a unique directory structure and name is critical.

### Template Usage

CSS file inclusion within templates should be done via the smarty `{css href=""}` tag.
 You do not need fully resolve your asset, as it will be done automatically providing you start your filename with `asset/`.

Example: The file `assets/css/awesome-component/mystyles.css` should be called with `{css href="assets/css/awesome-component/mystyles.css"}{/css}`.


## SCSS

Sass is a relatively newly supported system.
It provides a more powerful approach to styles, with the trade-off of requiring a bit more programming knowledge.

The directory structure for SCSS is just as specific as CSS files, since they compile down to flat .css by the compiler.
The root directory in your theme or component can vary based on if you want the SCSS file published along with your bundled up version.

If you __do not__ want your *.scss file published with production-version bundles, then place your file in `dev/assets/scss/some-name/`.

If you __do__ want your *.scss file published with production-version bundles, then place your file in `assets/scss/some-name/` or `assets/css/some-name/`.

SCSS files are compiled down to plain css files automatically when `utilities/compiler.php` is executed.
The directory of the output css file is also automatically remapped to `assets/css/some-name/`,
regardless if your scss file is in dev/ or not.

In addition to plain css files, a minified file is also generated and saved in `assets/css/some-name/filename.min.css`.
This minified file is sent to the browser when the configuration option minified javascript is set true.

This of course means that the drawback to SCSS files is that an additional action is required to generate these css files.
In Core, `utiltiies/compiler.php` will handle all the necessary work,
you just need to execute it via the command line whenever you change your styles.

### Template Usage

SCSS file inclusion within templates should be done via the smarty `{css href=""}` tag, just as with CSS files.

Example: The file `dev/assets/scss/awesome-component/mystyles.scss`
should be called with `{css href="assets/css/awesome-component/mystyles.css"}{/css}`.


## LESS

LESS files are the most recently added child of the three.
The directory of any less file must be within `assets/`, but since the resource is not compiled by Core,
can reside in `assets/css/some-name/` or `assets/less/some-name/`.

### Template Usage

LESS files are identical to every other style asset, and are called via the `{css}` smarty function.
These files do have an additional bit of functionality where the necessary javascript
is added to the page when Core detects a *.less file in the style list.
This is done automatically and no additional code is necessary on your part.

Example: The file `assets/less/awesome-component/mystyles.less`
should be called with `{css href="assets/less/awesome-component/mystyles.less"}{/css}`.