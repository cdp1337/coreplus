component.xml is the core connector for every component and the core.  It includes definitions on what the package contains, upgrade procedures to do automatically, requirements, provides and more.

## Upgrades

To declare a valid upgrade path, simply put `<upgrade from="prev.version" to="new.version"/>` inside your `<upgrades>` directive.

During development, the developer will many times queue up changes for the "next release" without necessarily knowing what that version number is.  A trick is to leave the "to" attribute blank and the "from" attribute set to the current version, and the packager will fill in the new version upon release.

    <upgrades>
        <upgrade from="1.0.0" to="1.1.0"/>
    <upgrades>

Versions should be done similarly to the Debian version number system, as illustrated on http://www.debian.org/doc/debian-policy/ch-controlfields.html#s-f-Version
Core uses one minor exceptions however, with the removal of the "epoch" number.  This means that Debian packages are often identified as "1:1.5-1".  The Core equivilant would be simply "1.5-1"
If you are extending an already-Core package, add the suffix "~" followed by your identifier and your version number.  An example of this could be "1.5-1~mypkg1", with "mypkg" being the identifier.

## Widgets

Widgets are classes, and as such get added as a `<class name="..."/>` directive under its respective filename.  This is the basic lookup directive for the system.  One specific feature of them is that they *must* extend `Widget_2_1` in order to work as a widget.

In order to make the widget's method administratively visible, (ie: in the Theme management utility), each method to be called must be within the `<widgets`> directive, for example.

    <widgets>
        <widget baseurl="/MyComponentWidget/DoSomething"/>
    </widgets>

## Dataset

The `<install>` and `<upgrade>` directives in components support the `<dataset>` element.  This allows the component developer to execute SQL-like statements using the `Dataset` system.