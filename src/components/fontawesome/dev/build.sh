#!/bin/bash
#
# Update the Font-Awesome library and apply Core changes to it.

echo "Loading basescript..."
source "/opt/eval/basescript.sh"

# One directory up from the dev is the component root.
COMPONENTDIR="$(readlink -f $(dirname $0)/../)"
# We're in the src directory anyway, only 3 up from here.
ROOTPDIR="$(readlink -f $(dirname $0)/../../../)"
# And dev is located 4 directories down from the root.
BASEPDIR="$(readlink -f $(dirname $0)/../../../../)"


if [ -e "$COMPONENTDIR/assets/css/font-awesome.css" ]; then
	PVERSION=$(egrep 'Font Awesome [0-9\.]+' "$COMPONENTDIR/assets/css/font-awesome.css" | sed 's:.*Awesome ::' | sed 's:^\([^ ]*\).*:\1:');

else
	PVERSION=""
fi


if [ ! -e "$COMPONENTDIR/assets/scss/_icons-original.scss" ]; then
	# If the icons original isn't present, force a re-download.
	PVERSION=""
fi

VERSIONTHERE=$(wget -q https://raw.github.com/FortAwesome/Font-Awesome/master/css/font-awesome.css -O - | egrep 'Font Awesome [0-9\.]+' | sed 's:.*Awesome ::' | sed 's:^\([^ ]*\).*:\1:');
if [ "$PVERSION" == "$VERSIONTHERE" ]; then
	echo "Skipping re-downloading of github project, no version change!"
else
	rm -fr "$COMPONENTDIR/assets"
	download git://https://github.com/FortAwesome/Font-Awesome.git "$COMPONENTDIR/assets"
	# I don't actually need many of these.
	rm -fr "$COMPONENTDIR/assets/.git"
	rm -fr "$COMPONENTDIR/assets/.gitignore"
	rm -fr "$COMPONENTDIR/assets/less"
	rm -fr "$COMPONENTDIR/assets/src"
	rm -fr "$COMPONENTDIR/assets/Gemfile"
	rm -fr "$COMPONENTDIR/assets/Gemfile.lock"
	rm -fr "$COMPONENTDIR/assets/_config.yml"
	rm -fr "$COMPONENTDIR/assets/component.json"
	rm -fr "$COMPONENTDIR/assets/composer.json"
	rm -fr "$COMPONENTDIR/assets/package.json"

	# And these are in the wrong directory!
	ls "$COMPONENTDIR/assets/scss/" | while read i; do
		mv "$COMPONENTDIR/assets/scss/$i" "$COMPONENTDIR/assets/css/"
	done
	rm -fr "$COMPONENTDIR/assets/scss/"

	mv "$COMPONENTDIR/assets/css/_icons.scss" "$COMPONENTDIR/assets/css/_icons-original.scss"
fi


# Font Awesome 1.2.3
VERSION=$(egrep 'Font Awesome [0-9\.]+' "$COMPONENTDIR/assets/css/font-awesome.css" | sed 's:.*Awesome ::' | sed 's:^\([^ ]*\).*:\1:');


## Add the render-icon mixin.
printheader "Installing Core mixins"
cat >> "$COMPONENTDIR/assets/css/_mixins.scss" <<EOS

/** Core mixin to actually create the icon. **/
@mixin render-icon(\$name, \$content){

	/** Icon definition for #{\$name} **/
	.#{\$fa-css-prefix}-#{\$name} {
		display: inline-block;
		font-family: FontAwesome;
		font-style: normal;
		font-weight: normal;
		line-height: 1;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}
	.#{\$fa-css-prefix}-#{\$name}:before {
		content: \$content;
	}
}

/** Core uses the "icon" prefix! **/
\$fa-css-prefix: icon;
EOS

## Re-index the icons file completely to use this new method.
printheader "Extracting current icon list"
tac "$COMPONENTDIR/assets/css/_icons-original.scss" > /tmp/fa-builder-icons.1

echo "/** Core-optimized icon set for FA **/" > "$COMPONENTDIR/assets/css/_icons.scss"

cat /tmp/fa-builder-icons.1 | while read i; do
	# Skip blank lines.
	if [ "$i" == "" ]; then continue; fi

	# Skip any line that does not begin with a "."
	if [ "${i:0:1}" != "." ]; then continue; fi

	# Is this a full command or an alias?
	if [ "$(echo $i | egrep ',$')" == "" ]; then
		# Full command!
		NAME="$(echo "$i" | sed 's#^.*\}-\([^:]*\):.*#\1#')"
		CONTENT="$(echo "$i" | sed 's#.*content: \([^;]*\).*#\1#')"

		echo "$CONTENT" > /tmp/fa-builder-icons.last
	else
		NAME="$(echo "$i" | sed 's#^.*\}-\([^:]*\):.*#\1#')"
		CONTENT="$(cat /tmp/fa-builder-icons.last)"
	fi

	echo "$NAME $CONTENT" >> /tmp/fa-builder-icons.2
done


## Add in a couple Core-specific icons.
cat >> /tmp/fa-builder-icons.2 <<EOS
add \$fa-var-plus
delete \$fa-var-minus
remove \$fa-var-minus
view \$fa-var-eye
directory \$fa-var-folder
move \$fa-var-bars
ok \$fa-var-check
folder-close \$fa-var-folder
exclamation-sign \$fa-var-exclamation-triangle
EOS

## Now I can re-assemble these back to their SCSS versions.
printheader "Re-joining icons into _icons.scss"
cat /tmp/fa-builder-icons.2 | sort | while read i; do
	NAME="$(echo $i | sed 's:^\([^ ]*\) \(.*\)$:\1:')"
	CONTENT="$(echo $i | sed 's:^\([^ ]*\) \(.*\)$:\2:')"
	echo "@include render-icon('$NAME', $CONTENT);" >> "$COMPONENTDIR/assets/css/_icons.scss"
done

## Cleanup
rm -fr /tmp/fa-builder-icons.1
rm -fr /tmp/fa-builder-icons.2
rm -fr /tmp/fa-builder-icons.last



if [ -e $BASEPDIR/utilities/packager.php ]; then
	$BASEPDIR/utilities/packager.php -r -c fontawesome
fi

if [ "$PVERSION" != "$VERSION" ]; then
	printline "Upgraded Font Awesome from $PVERSION to $VERSION"
fi
