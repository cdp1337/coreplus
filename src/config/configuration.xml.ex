<?xml version="1.0"?>
<!DOCTYPE configuration >
<configuration>
	<!--+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+~~~}
        |          IMPORTANT OPTIONS, DATABASE, SSL MODE, ETC.            |
        |                                                                 |
        | You probably need to edit most of the options in this section   |
    {~~~+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+~-->

	<return name="database_server" type="string" formtype="text" advanced="1">
		<value>localhost</value>
		<description>
			The server that will be hosting the database,
			usually leaving this localhost will suffice.
		</description>
	</return>

	<return name="database_port" type="int" formtype="text" advanced="1">
		<value>3306</value>
		<description>
			The port to connect to the database server.  Usually 3306 works here for mysql.
		</description>
	</return>

	<return name="database_type" type="enum" formtype="select" advanced="1">
		<value>mysqli</value>
		<description>
			The database type... (use mysqli here!)
		</description>
		<option>mysql</option>
		<option>mysqli</option>
		<option>cassandra</option>
	</return>

	<return name="database_name" type="string" formtype="text" advanced="0">
		<value>db_name</value>
		<description>
			Database name to connect to.
		</description>
	</return>

	<return name="database_user" type="string" formtype="text" advanced="0">
		<value>db_user</value>
		<description>
			Connect to the database as
		</description>
	</return>

	<return name="database_pass" type="string" formtype="password" advanced="0">
		<value>db_pass</value>
		<description>
			Password to use while connecting to the database
		</description>
	</return>

	<define name="SSL_MODE" type="enum" formtype="select">
		<value>disabled</value>
		<description><![CDATA[
<pre>
disabled - SSL is disabled completely
ondemand - SSL is allowed on pages that require it only, (standard pages redirect to non-ssl)
allowed  - SSL is allowed on any page throughout the site
required - SSL is always required for all pages
</pre>
		]]></description>
		<option>disabled</option>
		<option>ondemand</option>
		<option>allowed</option>
		<option>required</option>
	</define>

	<define name="SITENAME" type="string" formtype="text" advanced="0">
		<value>Core Plus Site</value>
		<description>The site name that can be used for emails and page titles.</description>
	</define>

	<return name="site_url" type="string" formtype="text">
		<value></value>
		<description>If a site url is provided and not blank, force the servername
			to match it. Useful for restricting access to www.domain.com.
			Please note, if you set this to an invalid location, you or anyone else will
			NOT be able to access the site.
		</description>
	</return>

	<define name="DEVELOPMENT_MODE" type="boolean" formtype="checkbox">
		<value>false</value>
		<description>If this is a production site, it is advised to disable this.</description>
	</define>

	<define name="SESSION_COOKIE_DOMAIN" type="string" formtype="text">
		<value></value>
		<description>
			If you would like to enforce a domain to be used for your cookies, set that here.
			For example, if you have sites on example1.domain.com, example2.domain.com, and
			www.domain.com, setting this value to ".domain.com" is recommended to have the sessions shared.
		</description>
	</define>

	<define name="FTP_USERNAME" type="string" formtype="text">
    		<value></value>
    		<description><![CDATA[
    			For any local file write access, providing the FTP username, password, and base directory will utilize
    			an FTP connection instead of direct writing.
    			<br/><br/>
    			This is useful for running the site as "www-data" or "apache" users, but having the files owned by a different user.
    		]]></description>
    	</define>

    	<define name="FTP_PASSWORD" type="string" formtype="password">
    		<value></value>
    		<description>FTP Password</description>
    	</define>

    	<define name="FTP_PATH" type="string" formtype="text">
    		<value></value>
    		<description>FTP Root Path</description>
    	</define>

    	<define name="CDN_TYPE" type="string" formtype="select">
    		<value>local</value>
    		<description>
    			The CDN type for asset and public files.  Choose "local" if you don't know what this means.
    		</description>
    		<option>local</option>
    		<!-- Disabling untested and unbuilt backends -->
    		<!--
    		<option>aws</option>
    		<option>rackspace</option>
    		-->
    	</define>

    	<define name="CDN_LOCAL_ASSETDIR" type="string" formtype="text">
    		<value>files/assets/</value>
    		<description>The asset (JS, CSS, Images, etc), resources that get access directly by the browser.</description>
    	</define>

    	<define name="CDN_LOCAL_PUBLICDIR" type="string" formtype="text">
    		<value>files/public/</value>
    		<description>The user-supplied and admin-supplied public uploads that get access directly by the browser.</description>
    	</define>

    	<define name="CDN_LOCAL_PRIVATEDIR" type="string" formtype="text">
            <value>files/private/</value>
            <description>The user-supplied and admin-supplied private uploads that cannot be accessed directly.</description>
        </define>

    	<!--
    	For AWS, I still need to support the following:
    	$p->assign('/core/aws/key', ConfigHandler::Get('/core/aws/key'));
    	$p->assign('/core/aws/secretkey', ConfigHandler::Get('/core/aws/secretkey'));
    	$p->assign('/core/aws/accountid', ConfigHandler::Get('/core/aws/accountid'));
    	$p->assign('/core/aws/canonicalid', ConfigHandler::Get('/core/aws/canonicalid'));
    	$p->assign('/core/aws/canonicalname', ConfigHandler::Get('/core/aws/canonicalname'));
    	-->



	<!--+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+~~~}
        |                       TECHNICAL OPTIONS                         |
        |                                                                 |
        | Technical things you probably don't need to worry about,        |
        |  but feel free to if you so choose                              |
    {~~~+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+~-->

	<define name="AUTO_INSTALL_ASSETS" type="boolean" formtype="checkbox">
		<value>false</value>
		<description>
			Auto-install component assets when in DEVELOPMENT_MODE.  This is a configurable
			option because it generally doubles the pageload speed.
			** Has no effect in production **
		</description>
	</define>


	<return name="tmp_dir_web" type="string" formtype="text">
		<value>/tmp/coreplus-web/</value>
		<description>
			The location of the tmp directory for cache, compile, and general temporary files.
			This directory MUST be writable by the apache user.
			Please ensure that this ends with a "/"
		</description>
	</return>

	<return name="tmp_dir_cli" type="string" formtype="text">
		<value>/tmp/coreplus-cli/</value>
		<description>
			The location of the tmp directory for anything on the CLI that needs temp storage.
			Please ensure that this ends with a "/"
		</description>
	</return>

	<define name="TIME_GMT_OFFSET" type="int" formtype="text">
		<value>0</value>
		<description>The number of seconds this machine is off from the current GMT time.</description>
	</define>

	<define name="TIME_DEFAULT_TIMEZONE" type="string" formtype="text">
		<value>America/New_York</value>
		<description>The default timezone to display times in.</description>
	</define>

	<define name="PORT_NUMBER" type="int" formtype="text">
		<value>80</value>
		<description>Port number server is listening on for normal connections.</description>
	</define>

	<define name="PORT_NUMBER_SSL" type="int" formtype="text">
		<value>443</value>
		<description>Port number server is listening on for secured connections.</description>
	</define>

	<return name="cache_type" type="enum" formtype="select">
		<value>file</value>
		<description>
			Leave this as file...
		</description>
		<option>apc</option>
		<option>file</option>
	</return>

	<define name="DB_PREFIX" type="string" formtype="text">
		<value></value>
		<description>
			Set this to something non-blank if you are running this system on the same database as other software.
		</description>
	</define>

	<!-- Uncomment this to change the GnuPG home directory to a more secure location. -->
	<!--
	<define name="GPG_HOMEDIR" type="string">
		<value>/path/to/secure/directory/gnupg</value>
	</define>
	-->

	<define name="DEFAULT_DIRECTORY_PERMS" type="octal" formtype="text">
		<value>0755</value>
		<description><![CDATA[<pre>
Default directory permissions to use for the system.
If security oriented, set as 0755.
If convenience is more important, set to 0777.
		</pre>
		]]></description>
	</define>

	<define name="DEFAULT_FILE_PERMS" type="octal" formtype="text">
		<value>0644</value>
		<description><![CDATA[<pre>
Default file permissions to use for the system.
If security oriented, set as 0644.
If convenience is more important, set to 0666.
</pre>
		]]></description>
	</define>

	<define name="ALLOW_NONXHR_JSON" type="boolean" formtype="checkbox">
		<value>false</value>
		<description>
			Debug variable, set this to true to allow calling *.json pages explicitly.
			By default this is set to false, so that json requests cannot proceed without at least the
			HTTP_X_REQUESTED_WITH header being set correctly.

			This by far is not an acceptable security measure to protect these assets, more of just a
			quick patch to keep the common passer-byer away from json data.
		</description>
	</define>

	<define name="SECRET_ENCRYPTION_PASSPHRASE" type="string" formtype="text">
		<value>RANDOM</value>
		<description>
			The encryption key used for sensitive information that must be saved in the database and retrieved as plain text.
			Storing the passphrase with the code is required beccause the encrypted data must be visible via the application.

			This does provide one level of security however, that is if the database is leaked, it would be difficult to
			decrypt those bits of information without the correct pass phrase.

			!!! IMPORTANT !!!  Once you set this and start using the site, DO NOT CHANGE IT!
			Doing so will make the encrypted data unusable!
		</description>
	</define>
</configuration>
