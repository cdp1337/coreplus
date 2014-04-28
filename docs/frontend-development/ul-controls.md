# Core UL Controls

Core has a javascript feature on <ul/> tags to convert them into page-contextual menus.

## Usage

To create a control menu, simply use the following markup:

	<ul class="controls">
		<li>
			{a href="/blah"}
				<i class="icon-blah"></i>
				Blah
			{/a}
		</li>
		<li>
            {a href="/foo"}
                <i class="icon-foo"></i>
                Foo
            {/a}
        </li>
	</ul>

This will create a simple context menu with all default options.

## Rendering

This control system will create a proxy element if there are more than 3 links to save space.
This proxy element will have an icon and optionally a text label.

## Options

Context controls do support a few options, which are specified in the <ul/> markup as data-* attributes.

* data-proxy-icon
	* Set to the string of the icon name (sans-"icon"), to use for the proxy.
* data-proxy-text
	* Set to the string of the proxy to use beside the icon.
* data-proxy-force
	* Set to "0" or "1" to force the use of the proxy.