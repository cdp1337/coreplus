Summary: List of defined constants available in Core Plus
Title: Defined Variables
Keywords: Core Plus
          define
          constant

# Defined Constants

A list of defined constants in Core Plus and some information about what's contained in them.

All defined constants can be called from a controller or model via the name listed,
and can be used from within a Smarty templates via `$smarty.const.CONSTANT_NAME`.
For PHP templates, please just use `<?php CONSTANT_NAME; ?>`.


## User Geolocation Constants

### REMOTE_CITY

**DYN**[^dynamic-constant]
The city of the remote user pulled from any geolocation database available via the IP address.

    example: "Columbus", "", "New York", etc.

### REMOTE_PROVINCE

**DYN**[^dynamic-constant]
The province or state ISO code of the remote user

    example: "OH", "IN", etc.

### REMOTE_COUNTRY

**DYN**[^dynamic-constant]
The country ISO code of the remote user

    example: "US", "DE", "AQ", etc.

### REMOTE_TIMEZONE

**DYN**[^dynamic-constant]
The timezone of the remote user

    example: "America/New_York", etc.

### REMOTE_POSTAL

**DYN**[^dynamic-constant]
The postal code of the remote user

    example: "43215", NULL

Note, this define CAN be NULL if the IP does not resolve to a valid address.

### REMOTE_IP

**DYN**[^dynamic-constant]
The remote IP of the connecting computer.
Based dynamically off the $_SERVER variable.

    example: "10.0.1.2", "1.2.3.4", "1234:5678:90ab:cdef:013f:ed84"


## Useful System Defines

### SITENAME

**XML**[^xml-config-constant]
The site name that can be used for emails and page titles.

### ROOT_PDIR

The physical directory of the Core Plus installation.  DOES have a trailing slash.

    Example: /home/someone/public_html/myinstall/

### ROOT_WDIR

The location of the root installation based on the browser get string.  DOES have a trailing slash.

    Example: /~someone/myinstall/

### HOST

Host is a more human-friendly version of SERVERNAME.
It does not include port number or protocol, but just the hostname itself.

    example: domain.tld

### SERVERNAME

Full URL of server.

    example: http://www.example.com or https://127.0.0.1:8443

### SERVERNAME_NOSSL

Full URL of the server forced non-ssl mode.

    example: http://www.example.com

### SERVERNAME_SSL

Full URL of the server forced SSL mode.

    example: https://www.example.com or https://127.0.0.1:8443

### ROOT_URL

URL of web root.

    example: http://www.example.com/foo/man/choo/

### ROOT_URL_NOSSL

URL of web root.

    example: http://www.example.com/foo/man/choo/

### ROOT_URL_SSL

URL of web root.

    example: https://www.example.com/foo/man/choo/

### CUR_CALL

Current call/request.

    example: /foo/man/choo/?somevariable=true&somethingelse=false

### REL_REQUEST_PATH

Relative requested path.

    example: /User/Login or '/' for the index.

### SSL

Simple true/false if current page call is via SSL.


## Time Defines

    define('SECONDS_ONE_MINUTE', 60);
    define('SECONDS_ONE_HOUR',   3600);
    define('SECONDS_TWO_HOUR',   7200);
    define('SECONDS_ONE_DAY',    86400);
    define('SECONDS_ONE_WEEK',   604800);  // 7 days
    define('SECONDS_TWO_WEEK',   1209600); // 14 days
    define('SECONDS_ONE_MONTH',  2629800); // 30.4375 days
    define('SECONDS_TWO_MONTH',  5259600); // 60.8750 days


## Other/System Defines

### SERVER_ADMIN_EMAIL

**XML**[^xml-config-constant]
Set this to an email address, (or comma-separated list for multiple), to receive server health reports and critical errors.

### SERVER_ID

**XML**[^xml-config-constant]
The server ID when used in a multi-server environment, leave empty for "1" (default).

### DEVELOPMENT_MODE

**XML**[^xml-config-constant]
Check if this site will be used in a development environment.  Extra debugging information and verbose error messages are enabled if this is checked.

### SESSION_COOKIE_DOMAIN

**XML**[^xml-config-constant]
If you would like to enforce a domain to be used for your cookies, set that here.
For example, if you have sites on example1.domain.com, example2.domain.com, and
www.domain.com, setting this value to ".domain.com" is recommended to have the sessions shared.

### FTP_USERNAME

**XML**[^xml-config-constant]
For any local file write access, providing the FTP username, password, and base directory will utilize an FTP connection instead of direct writing.

This is useful for running the site as "www-data" or "apache" users, but having the files owned by a different user.

### FTP_PASSWORD

**XML**[^xml-config-constant]
FTP Password

### FTP_PATH

**XML**[^xml-config-constant]
FTP Root Path

### CDN_TYPE

**XML**[^xml-config-constant]
The CDN type for asset and public files.  Choose "local" if you don't know what this means.

### CDN_LOCAL_ASSETDIR

**XML**[^xml-config-constant]
The asset (JS, CSS, Images, etc), resources that get access directly by the browser.

### CDN_LOCAL_PUBLICDIR

**XML**[^xml-config-constant]
The user-supplied and admin-supplied public uploads that get access directly by the browser.

### CDN_LOCAL_PRIVATEDIR

**XML**[^xml-config-constant]
The user-supplied and admin-supplied private uploads that cannot be accessed directly.

### CDN_FTP_USERNAME

**XML**[^xml-config-constant]
FTP Username to push/pull files over an FTP connection for use as a CDN.

### CDN_FTP_PASSWORD

**XML**[^xml-config-constant]
FTP Password to push/pull files over an FTP connection for use as a CDN.

### CDN_FTP_HOST

**XML**[^xml-config-constant]
FTP base path to push/pull files over an FTP connection for use as a CDN.

This is usually a fully resolved path starting and ending with a slash.

### CDN_FTP_PATH

**XML**[^xml-config-constant]
FTP base path to push/pull files over an FTP connection for use as a CDN.

This is usually a fully resolved path starting and ending with a slash.

### CDN_FTP_URL

**XML**[^xml-config-constant]
The URL used as a base for all FTP CDN resources.
This must be the URL form of your base CDN_FTP_PATH variable.

eg: If your FTP path is /home/user/public_html/content/,
then your URL may be cdn.domain.tld/~user/content/.

You do not need to include the HTTP:// or HTTPS:// prefix, as that is added automatically.

### CDN_FTP_ASSETDIR

**XML**[^xml-config-constant]
The asset (JS, CSS, Images, etc), resources that get access directly by the browser.

### CDN_FTP_PUBLICDIR

**XML**[^xml-config-constant]
The user-supplied and admin-supplied public uploads that get access directly by the browser.

### CDN_FTP_PRIVATEDIR

**XML**[^xml-config-constant]
The user-supplied and admin-supplied private uploads that cannot be accessed directly.

### AUTO_INSTALL_ASSETS

**XML**[^xml-config-constant]
Auto-install any modified asset resource when in DEVELOPMENT_MODE and using a local CDN.

### XHPROF

**XML**[^xml-config-constant]
Set to a value greater than 0 to enable the XHprof profiler that percentage of the time.
For example, setting to 100 will enable the profiler on every page; setting to 50 will enable the profiler on 50% of the page views.

### TIME_GMT_OFFSET

**XML**[^xml-config-constant]
The number of seconds this machine is off from the current GMT time.

### TIME_DEFAULT_TIMEZONE

**XML**[^xml-config-constant]
The default timezone to display times in.

### PORT_NUMBER

**XML**[^xml-config-constant]
Port number server is listening on for normal connections.

### PORT_NUMBER_SSL

**XML**[^xml-config-constant]
Port number server is listening on for secured connections.

### DB_PREFIX

**XML**[^xml-config-constant]
Set this to something non-blank if you are running this system on the same database as other software.

### GPG_HOMEDIR

**XML**[^xml-config-constant]
text

### DMI_QUERY_LOG_TIMEOUT

**XML**[^xml-config-constant]
Set to a number >= 0 to record queries that take that long in milliseconds to execute.  AKA "slow query log".

(Hint, most reads should complete within 4ms and writes should complete within 10ms, otherwise something may be wrong with the server.)

* Set to "-1" to disable query logging altogether (default)
* Set to "0" to log all queries to logs/query.log
* Set to "10" to log queries taking longer than 10ms to logs/query.log
* Set to "1000" to log queries taking longer than 1 second to logs/query.log
* Set to "2000" to log queries taking longer than 2 seconds to logs/query.log
* etc...

### DEFAULT_DIRECTORY_PERMS

**XML**[^xml-config-constant]
Default directory permissions to use for the system.
If security oriented, set as 0755.
If convenience is more important, set to 0777.

### DEFAULT_FILE_PERMS

**XML**[^xml-config-constant]
Default file permissions to use for the system.
If security oriented, set as 0644.
If convenience is more important, set to 0666.

### ALLOW_NONXHR_JSON

**XML**[^xml-config-constant]
Debug variable, set this to true to allow calling *.json pages explicitly.
By default this is set to false, so that json requests cannot proceed without at least the
HTTP_X_REQUESTED_WITH header being set correctly.

This by far is not an acceptable security measure to protect these assets, more of just a
quick patch to keep the common passer-byer away from json data.

### SECRET_ENCRYPTION_PASSPHRASE

**XML**[^xml-config-constant]
The encryption key used for sensitive information that must be saved in the database and retrieved as plain text.
Storing the passphrase with the code is required beccause the encrypted data must be visible via the application.

This does provide one level of security however, that is if the database is leaked, it would be difficult to
decrypt those bits of information without the correct pass phrase.

!!! IMPORTANT !!!  Once you set this and start using the site, DO NOT CHANGE IT!
Doing so will make the encrypted data unusable!

### EXEC_MODE

The execution mode of the page.  This is not overly useful anymore, but is still present.

### FULL_DEBUG

FULL_DEBUG is useful for the core development of the platform.

### NL

Literally "\n".

### TAB

Literally "\t".

### DS

Shorthand directory separator constant




// Line color, the separating characters
	define('COLOR_LINE', "<span style='color:grey; font-family:Courier,mono;'>");
	// Heading color
	define('COLOR_HEADER', "<span style='color:cyan; font-weight:bold; font-family:Courier,mono;'>");
	// Success color
	define('COLOR_SUCCESS', "<span style='color:green; font-weight:bold; font-family:Courier,mono;'>");
	// Warning color
	define('COLOR_WARNING', "<span style='color:yellow; font-weight:bold; font-family:Courier,mono;'>");
	// Error color
	define('COLOR_ERROR', "<span style='color:red; font-weight:bold; font-family:Courier,mono;'>");
	// Debug color
	define('COLOR_DEBUG', "<span style='color:lightskyblue; font-family:Courier,mono;'>");
	// Normal color, no styles applied, required because any RESET (</span>) needs a start span.
	define('COLOR_NORMAL', "<span style='font-family:Courier,mono;'>");
	// Reset color
	define('COLOR_RESET', "</span>");
	// Space character
	define('NBSP', '&nbsp;');
	
	// Line color, the separating characters
    	define('COLOR_LINE', "\033[0;30m");
    	// Heading color
    	define('COLOR_HEADER', "\033[1;36m");
    	// Success color
    	define('COLOR_SUCCESS', "\033[1;32m");
    	// Warning color
    	define('COLOR_WARNING', "\033[1;33m");
    	// Error color
    	define('COLOR_ERROR', "\033[1;31m");
    	// Debug color
    	define('COLOR_DEBUG', "\033[0;34m");
    	// Normal color, alias of RESET for CLI operation, but has other meaning on WEB operation.
    	define('COLOR_NORMAL', "\033[0m");
    	// Reset color
    	define('COLOR_RESET', "\033[0m");
    	// Space character
    	define('NBSP', ' ');


if(!defined('TMP_DIR')) {
	/**
	 * Temporary directory
	 */
	define('TMP_DIR', $tmpdir);
}

/**
 * Temporary directory for web only
 * (useful in the packager)
 */
define('TMP_DIR_WEB', $core_settings['tmp_dir_web']);

/**
 * Temporary directory for cli only
 * (useful in the packager)
 */
define('TMP_DIR_CLI', $core_settings['tmp_dir_cli']);



/**
 * The GnuPG home directory to store keys in.
 */
if (!defined('GPG_HOMEDIR')) {
	define('GPG_HOMEDIR', ($gnupgdir) ? $gnupgdir : ROOT_PDIR . 'gnupg');
}

if(XHPROF > 0 && function_exists('xhprof_enable')){
	if(XHPROF == 100){
		define('ENABLE_XHPROF', true);
	}
	else{
		define('ENABLE_XHPROF', (XHPROF > rand(1,100)));
	}
}
else{
	define('ENABLE_XHPROF', false);
}


[^dynamic-constant]: **Dynamically Generated Constants** are generated on-the-fly based on information in the environment and is non-editable.
[^xml-config-constant]: **XML Configurable Constants** can be changed by editing config/configuration.xml with a plain-text editor such as VIM, Nano, emacs, gedit, or any other plain-text editor.  It is advised to create a backup before editing any critical file on Core.