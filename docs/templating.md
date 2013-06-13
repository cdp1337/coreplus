# Templating in Core Plus

Templating in Core Plus is generally done with the Smarty templating engine, although additional template parsers could be switched in, (as of 2.5.1).

## Types of Templates

Given that Core Plus is a strictly MVC based framework, templates, (or the "View" part of MVC), play a critical role in virtually everything.

### Skin

Every theme requires at least two skins.  One template is used as the main site template wrapping every page request, and another "blank.tpl", is a system template used to render pages with only the `<body>` tag and other critial elements, (no navigation, no logo, minimal header/footer, etc).

These files MUST be located under `themes/[theme name]/skins/`

There can be an unlimited number of additional skins available for the admin to pick from.

#### Variables

* title - The title of the page
	* string
* seotitle - The search optimized title of the page
	* string
* controls - Page level controls to render out
	* ViewControls
	* These generally provide administration functions that can be performed on the given page.
* breadcrumbs - Page breadcrumbs
	* array
* messages - System messages to display out
	* array of arrays (`[ type => '...', text => '...' ]`)
* body - The body provided by the application
	* string

### Pages

Nearly every component has a page template for each different page view used; such as `index.tpl`, `view.tpl`, `update.tpl`, `create.tpl`.  Two or more pages can also share a single template such as `create_update.tpl`, or other pages that share very similar functionality.  Pages are normally stored in `components/[component name]/pages/`.

To override a template, copy the template into `themes/[theme name]/pages/` to match the directory structure.

### Widgets
### Emails
### Form Elements

## TinyMCE Notes

Components can have their stylesheets overwrote in each theme, and sometimes this is useful at the core level.  TinyMCE makes use of this by providing a stylesheet called "`css/tinymce/content.css`".  This provides the content styles to be used within the editor.  Check "`base-v2/assets/css/tinymce/content.css`" for a working example.