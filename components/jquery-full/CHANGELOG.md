1.7.1~core2
2012.07.20
Added jquery.cookie plugin.

<changelog version="1.7.0-1">
	<notes>
		New upstream release.
		Upgraded jquery.readonly to release 2.0.1
		Switched to a more pure Debian-style versioning system
	</notes>
	<packagemeta>
		<date>Sat, 19 Nov 2011 03:09:21 +0000</date>
		<maintainer name="Charlie Powell" email="charlie@eval.bz"/>
		<packager>CAE2 0.0.9</packager>
	</packagemeta>
</changelog>

<changelog version="1.6.4~core1">
	<packagemeta>
		<date>Mon, 17 Oct 2011 23:49:06 -0400</date>
		<maintainer name="Charlie Powell" email="charlie@eval.bz"/>
		<packager>CAE2 0.0.7</packager>
	</packagemeta>
</changelog>

<changelog version="1.5.1~cae3">
	<packagemeta>
		<date>Thu, 06 Oct 2011 22:08:52 -0400</date>
		<maintainer name="Charlie Powell" email="charlie@eval.bz"/>
		<packager>CAE2 0.0.6</packager>
	</packagemeta>
	<notes>
		Set the included libraries to be 'library' instead of 'component'.  This allows them to be "required" by other components via &lt;require name="JQuery.plugin.desired" type="library"/&gt;
	</notes>
</changelog>

<changelog version="1.5.1~cae2">
	<packagemeta>
		<date>Tue, 21 Jun 2011 22:56:08 -0400</date>
		<maintainer name="Charlie Powell" email="charlie@eval.bz"/>
		<packager>CAE2 0.0.1-dev1</packager>
	</packagemeta>
	<notes>
		Switch the system to use "/core/javascript/minified" configuration option instead of "/jquery/minfied".  This is because other systems may use the same logic to determine if the minified versions should be used or standard/dev.
	</notes>
</changelog>

<changelog version="1.5.1~cae1">
	<packagemeta>
		<date>Sat, 18 Jun 2011 02:11:08 -0400</date>
		<maintainer name="Charlie Powell" email="charlie@eval.bz"/>
		<packager>CAE2 0.0.1-dev1</packager>
	</packagemeta>
</changelog>