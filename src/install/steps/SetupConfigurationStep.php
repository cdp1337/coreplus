<?php
/**
 * File for class InstallConfigurationStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130320.2149
 * @package Core\Installer
 */

namespace Core\Installer;


/**
 * Provide the user a UI to set configuration values that will get saved back to the configuration.xml file.
 * 
 * @package Core\Installer
 */
class SetupConfigurationStep extends InstallerStep{
	public function execute(){

		// If there's already a configuration file present... just skip to the next.
		if(file_exists(ROOT_PDIR . '/config/configuration.xml')){
			$this->setAsPassed();
			reload();
		}

		// This will contain the temporary configuration values for the installer.
		if(!isset($_SESSION['configs'])) $_SESSION['configs'] = [];

		$xml = new \XMLLoader();
		$xml->setRootName('configuration');
		$xml->loadFromFile(ROOT_PDIR . 'config/configuration.example.xml');
		$formelements = [];

		// Since we're pulling from the ant version, set some nice defaults for the user.
		$valuedefaults = [
			'@{db.server}@' => 'localhost',
			'@{db.port}@' => '3306',
			'@{db.type}@' => 'mysqli',
			'@{db.name}@' => '',
			'@{db.user}@' => '',
			'@{db.pass}@' => '',
			'@{devmode}@' => 'false',
			'/tmp/coreplus-web/' => '/tmp/' . $_SERVER['HTTP_HOST'] . '-web/',
			'/tmp/coreplus-cli/' => '/tmp/' . $_SERVER['HTTP_HOST'] . '-cli/',
			'RANDOM' => \Core\random_hex(96),
		];

		$elements = $xml->getElements('return|define');
		foreach($elements as $el){
			$node        = $el->nodeName;
			$name        = $el->getAttribute('name');
			$type        = $el->getAttribute('type');
			$formtype    = $el->getAttribute('formtype');
			$advanced    = $el->getAttribute('advanced');
			$children    = $el->childNodes;
			$value       = null;
			$valuenode   = null;
			$description = null;
			$options     = [];

			// Defaults
			if($advanced === null || $advanced === '') $advanced = "1";

			foreach($children as $c){
				switch($c->nodeName){
					case 'value':
						$value = trim($c->nodeValue);
						$valuenode = $c;
						break;
					case 'description':
						$description = trim($c->nodeValue);
						break;
					case 'option':
						$options[] = trim($c->nodeValue);
						break;
					case '#text':
						break;
					case '#comment':
						break;
					default:
						trigger_error('Unknown sub-node for ' . $node . ' ' . $name . ': ' . $c->nodeName);
				}
			}

			// Since we're pulling from the ant version, set some nice defaults for the user.
			if(isset($valuedefaults[$value])){
				$value = $valuedefaults[$value];
			}

			// Save the value?
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				if($type == 'boolean' && $formtype == 'checkbox'){
					$value = isset($_POST[$name]) ? 'true' : 'false';
				}
				else{
					$value = isset($_POST[$name]) ? $_POST[$name] : '';
				}

				$_SESSION['configs'][$name] = $value;
			}
			elseif(isset($_SESSION['configs'][$name])){
				$value = $_SESSION['configs'][$name];
			}

			//$value = $el->getElement('value')->nodeValue;

			// Throw this element onto the array for the template to render out.
			$formelements[] = [
				'name'        => $name,
				// Make the title more appealing than machine names...
				'title'       => ucwords(strtolower(str_replace('_', ' ', $name))),
				// Remap "formtype" to "type", since this will be used in a form afterall!
				'type'        => $formtype,
				'value'       => $value,
				'description' => $description,
				'options'     => $options,
				'advanced'    => $advanced,
			];
		}


		// If it's a POST... try the settings and if valid, proceed.
		$message = null;
		$instructions = null;

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($message === null){
				$connectionresults = $this->testDatabaseConnection();
				if($connectionresults['status'] != 'passed'){
					//var_dump($connectionresults); die();
					$message = $connectionresults['message'];
					$instructions = $connectionresults['instructions'];
				}
			}

			if($message === null){
				// Test the assets too!
				$results = $this->testDirectoryWritable('assets/');
				if($results['status'] != 'passed'){
					//var_dump($connectionresults); die();
					$message = $results['message'];
					$instructions = $results['instructions'];
				}
			}

			if($message === null){
				// Test the assets too!
				$results = $this->testDirectoryWritable('public/');
				if($results['status'] != 'passed'){
					//var_dump($connectionresults); die();
					$message = $results['message'];
					$instructions = $results['instructions'];
				}
			}


			if($message === null){
				// Still null after all the tests have ran?
				// w00t!
				$this->setAsPassed();
				reload();
			}
		}

		$this->getTemplate()->assign('message', $message);
		$this->getTemplate()->assign('instructions', $instructions);
		$this->getTemplate()->assign('formelements', $formelements);
		//var_dump($formelements);// die();
	}

	/**
	 * Test the database connection given the SESSION data.
	 * Will gracefully handle exceptions.
	 *
	 * @return array
	 */
	private function testDatabaseConnection(){

		try{
			require_once(ROOT_PDIR . 'core/libs/core/datamodel/DMI.class.php');
			$dbconn = \DMI::GetSystemDMI();
		}
		// The server can't be located
		catch(\DMI_ServerNotFound_Exception $e){
			return [
				'status' => 'failed',
				'message' => $e->getMessage(),
				'instructions' => '',
			];
		}
		// This is specific to user denied.
		catch(\DMI_Authentication_Exception $e){
			return [
				'status' => 'failed',
				'message' => $e->getMessage(),
				'instructions' => $this->getDatabaseUserInstructions(),
			];
		}
		// Any other error.
		// Couldn't establish connection... do something fun!
		catch(\Exception $e){
			return [
				'status' => 'failed',
				'message' => $e->getMessage(),
				'instructions' => $this->getDatabaseDatabaseInstructions(),
			];
		}

		// w00t, it must have worked!
		return ['status' => 'passed', 'message' => 'Connection succeeded', 'instructions' => ''];
	}

	/**
	 * Get the "DMI_Authentication_Exception" instructions for a given database backend, (based on session data)
	 *
	 * @return string
	 */
	private function getDatabaseUserInstructions(){
		switch($_SESSION['configs']['database_type']){

			// Cassandra connection information
			case 'cassandra':
				$instructions = <<<EOD
<p>
	Please execute the following commands with cassandra-cli or another interface.
</p>
<pre>create keyspace %dbname%;</pre>
EOD;
				$instructions = sprintf($instructions, $_SESSION['configs']['database_name']);
				break;

			// Mysql/Mysqli connection information
			case 'mysql':
			case 'mysqli':
				$instructions = <<<EOD
<p>
	Seems as you have either an incorrect password or the user does not exist.
	If you wish to create the mysql user, please execute the following commands with mysql or another interface,
	(like phpMyAdmin, toad, or the mysql CLI).
</p>
<pre>
CREATE USER '%s' IDENTIFIED BY '%s';
FLUSH PRIVILEGES;
</pre>

<p>
	IF... doing the above still results in an access denied for user error,
	remove your anonymous localhost user!  Alternatively,
	just change the USER directive to '%s'@'localhost'
</p>

EOD;
				$instructions = sprintf(
					$instructions,
					$_SESSION['configs']['database_user'],
					$_SESSION['configs']['database_pass'],
					$_SESSION['configs']['database_user']
				);
				break;

			// Other???
			default:
				die("<p class='error-message'>I don't know what datamodel store you're trying to use, but I don't support it...</p>");
				break;
		}

		return $instructions;
	}

	/**
	 * Get the generic "Exception" instructions for a given database backend, (based on session data)
	 *
	 * @return string
	 */
	private function getDatabaseDatabaseInstructions(){
		switch($_SESSION['configs']['database_type']){

			// Cassandra connection information
			case 'cassandra':
				$instructions = <<<EOD
<p>
	Please execute the following commands with cassandra-cli or another interface.
</p>
<pre>create keyspace %dbname%;</pre>
EOD;
				$instructions = sprintf($instructions, $_SESSION['configs']['database_name']);
				break;

			// Mysql/Mysqli connection information
			case 'mysql':
			case 'mysqli':
				$instructions = <<<EOD
<p>
	Seems as you have either an incorrect password or the user does not exist.
	If you wish to create the mysql user, please execute the following commands with mysql or another interface,
	(like phpMyAdmin, toad, or the mysql CLI).
</p>

<pre>
CREATE DATABASE IF NOT EXISTS %s;
GRANT ALL ON %s.* TO '%s';
FLUSH PRIVILEGES;
</pre>
EOD;
				$instructions = sprintf(
					$instructions,
					$_SESSION['configs']['database_name'],
					$_SESSION['configs']['database_name'],
					$_SESSION['configs']['database_user']
				);
				break;

			// Other???
			default:
				die("<p class='error-message'>I don't know what datamodel store you're trying to use, but I don't support it...</p>");
				break;
		}

		return $instructions;
	}

	private function testDirectoryWritable($dir){
		
		// The configuration wouldn't be ready yet... make sure the calling methods have the appropriate constants defined.
		if(!defined('FTP_USERNAME')){
			define('FTP_USERNAME', $_SESSION['configs']['FTP_USERNAME']);
		}
		if(!defined('FTP_PASSWORD')){
			define('FTP_PASSWORD', $_SESSION['configs']['FTP_PASSWORD']);
		}
		if(!defined('FTP_PATH')){
			define('FTP_PATH', $_SESSION['configs']['FTP_PATH']);
		}
		if(!defined('CDN_TYPE')){
			define('CDN_TYPE', $_SESSION['configs']['CDN_TYPE']);
		}
		if(!defined('CDN_LOCAL_ASSETDIR')){
			define('CDN_LOCAL_ASSETDIR', $_SESSION['configs']['CDN_LOCAL_ASSETDIR']);
		}
		if(!defined('CDN_LOCAL_PUBLICDIR')){
			define('CDN_LOCAL_PUBLICDIR', $_SESSION['configs']['CDN_LOCAL_PUBLICDIR']);
		}

		/** @var $dir \Directory_Backend */
		$dir = \Core\directory($dir);
		if(!$dir->isWritable()){
			$dirname = $dir->getPath();
			$whoami = trim(`whoami`);
			$instructions = <<<EOD
<strong>GUI, FTP, or Web Management Method</strong>
<p>
Right click on the directory and set "group" and "other" to writable and executable.
<p>
<strong>CLI Lazy (insecure) Method</strong>
<p>
<pre>chmod -R a+wx "$dirname"</pre>
</p>
<strong>CLI Secure Method</strong>
<p>
<pre>sudo chown -R $whoami "$dirname"</pre>
</p>
EOD;
			return [
				'status' => 'failed',
				'message' => $dir->getPath() . ' is not writable.',
				'instructions' => $instructions,
			];
		}
		else{
			return ['status' => 'passed', 'message' => $dir->getPath() . ' is writable.', 'instructions' => ''];
		}
	}
}
