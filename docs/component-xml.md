# Component XML

Every component in Core Plus must have a special file named "`component.xml`", located in the component's root directory.  This file provides information to Core about the component, included files, scripts, upgrades, etc.


## Necessary Headers

There are two necessary headers that must be present at the top of the file before the `<component>` start.

	<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE component SYSTEM "http://corepl.us/api/2_1/component.dtd">

The `<?xml ... ?>` line defines the XML file and its encoding type.  This is standard for all XML files.

The `<!DOCTYPE ... >` line sets up the root node and a path to its schema.  This is used by editors and validators, as well as by Core itself to ensure that it's a supported version of the file.

These two lines MUST be EXACTLY like that and MUST be present.


## Assets Directive

@todo Write this up


## Authors Directive

@todo Write this up


## Configs Directive

@todo Write this up


## Files Directive

@todo Write this up


## Forms Directive

@todo Write this up


## Hooks Directive

@todo Write this up


## Includes Directive

@todo Write this up


## Install Directive

@todo Write this up


## Licenses Directive

@todo Write this up


## Pages Directive

Pages can be defined to be created immediately in the system.  These are useful for admin-level pages, special pages like login, etc.  The `<pages>` directive has no attributes, but contains `<page>` directives.


### Attributes for `<page>`

* baseurl
	* [required] The baseurl of this page, ensure to start it with a '/'.
* title
	* [required] The title of this page.
* access
	* Access string of users who can view this page
* admin
	* "0" or "1" if this should be considered an "admin" page.
* selectable
	* "0" or "1" if this page should be selectable as a navigatable page and displayed in the sitemap.

### Example

	<pages>

		<!-- Create a standard, selectable page -->
		<page baseurl="/mycontroller/awesomemethod" title="My Awesome Method"/>

		<!-- 
		Create an admin page, this will be displayed in the admin bar and is not going to 
		show up in the sitemap nor as a selectable page in navigation and other systems. 
		-->
		<page baseurl="/mycontroller/admin" title="Method Admin" admin="1" selectable="0"/>
	</pages>


## Permissions Directive

@todo Write this up


## Requires Directive

The `<requires>` directive sets the required files and components that must be present in the system in order for this component to be installed.  The directive itself has no attributes, but `<require>` statements are contained herein.

A common line present in virtually every component is `<require name="core" type="component" version="2.4.4"/>`.  This requires Core to be installed and at version of at least 2.4.4.

### Attributes for `<require>`

* name
	* [required] The name of the library or component to require
* type
	* [required] The type of the require, can be "component" or "library".
* version
	* The version of the thing to require.
* operation
	* The operation to use for version checking.  Can be "ge", "gt", "le", "lt", "eq".  Default is "ge", greater than or equal to.

### Example

	<requires>

		<!-- This component requries Core 2.4.4 or greater -->
		<require name="core" type="component" version="2.4.4"/>

		<!-- This component requries the jquery library to be available, any version. -->
		<require name="jquery" type="library"/>

	</requires>

## SmartyPlugins Directive

@todo Write this up


## Upgrades

To declare a valid upgrade path, simply put `<upgrade from="prev.version" to="new.version"/>` inside your
`<upgrades>` directive.

During development, the developer will many times queue up changes for the "next release" without necessarily
knowing what that version number is.  A trick is to leave the "to" attribute blank and the "from" attribute set to the
current version, and the packager will fill in the new version upon release.

    <upgrades>
        <upgrade from="1.0.0" to="1.1.0"/>
    <upgrades>

Versions should be done similarly to the Debian version number system, as illustrated on
http://www.debian.org/doc/debian-policy/ch-controlfields.html#s-f-Version
Core uses one minor exceptions however, with the removal of the "epoch" number.  This means that Debian packages are
often identified as "1:1.5-1".  The Core equivilant would be simply "1.5-1"
If you are extending an already-Core package, add the suffix "~" followed by your identifier and your version number.
An example of this could be "1.5-1~mypkg1", with "mypkg" being the identifier.

## Upgrade and Install options

`<upgrade>` and `<install>` directives both support the same subelements

### Dataset

The `<install>` and `<upgrade>` directives in components support the `<dataset>` element.  This allows the component
developer to execute SQL-like statements using the `Dataset` system.

## UserConfigs Directive

@todo Write this up


## View Directive

@todo Write this up



## Widgets

Widgets are classes, and as such get added as a `<class name="..."/>` directive under its respective filename.
This is the basic lookup directive for the system.  One specific feature of them is that they *must* extend
`Widget_2_1` in order to work as a widget.

In order to make the widget's method administratively visible, (ie: in the Theme management utility), each method to be
called must be within the `<widgets`> directive, for example.

    <widgets>
        <widget baseurl="/MyComponentWidget/DoSomething"/>
    </widgets>


