<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase /~powellc/Projects/cae2/git-master/


	RewriteCond %{SCRIPT_FILENAME} -f [OR]
	RewriteCond %{SCRIPT_FILENAME} -d
	RewriteRule ^(.+) - [PT,L]
	RewriteRule ^(.*) index.php%{REQUEST_URI}
</IfModule>

