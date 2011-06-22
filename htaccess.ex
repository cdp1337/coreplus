<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase /~powellc/Projects/cae2/git-master/


	## Ignore these directories.
	#RewriteCond  %{REQUEST_URI}       /assets       [OR]           # Ignore anything in the assets directory
	#RewriteCond  %{REQUEST_URI}       /install                 # Ignore anything in the install directory
	#RewriteRule  (.*)                 $1            [L]
	#
	#
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule (.*) index.php%{REQUEST_URI} [L]
</IfModule>

