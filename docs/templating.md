# Templating in Core Plus

Templating in Core Plus is generally done with the Smarty templating engine, although additional template parsers could be switched in relatively easily, (as of 2.5.1 at least).

## Types of Templates

Given that Core Plus is a strictly MVC based framework, templates, (or the "View" part of MVC), play a critical role in virtually everything.

### Skin

Every theme requires at least one skin.  This template is used as the main site template, including all headers, footers, sections, etc.

These files MUST be located under `themes/[theme name]/skins/`

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
### Widgets
### Emails
### Form Elements