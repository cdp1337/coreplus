# Core UL Controls

Core has a javascript feature on &lt;ul/&gt; tags to convert them into page-contextual menus.

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

Context controls do support a few options, which are specified in the &lt;ul/&gt; markup as data-* attributes.

### data-proxy-icon

Set to the string of the icon name (sans-"icon"), to use for the proxy.

### data-proxy-text

Set to the string of the proxy to use beside the icon.

### data-proxy-force

Set to "0" to force no proxy, or "1" to force the proxy.

### data-proxy-icon-animation

Set to "spin", "bounce", "float", or any of the other supported font-awesome animations
to make the proxy icon animated on page load and mouse over.

### data-only-icons

Set to 1 to enable only displaying of icons instead of the default icons+text.
Also available with the class name "only-icons".