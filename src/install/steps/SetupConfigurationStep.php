<?php
/**
 * File for class InstallConfigurationStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130320.2149
 * @package Core\Installer
 */

namespace Core\Installer;

require_once(ROOT_PDIR . 'core/libs/core/BaconIpsumGenerator.class.php');

/**
 * Provide the user a UI to set configuration values that will get saved back to the configuration.xml file.
 * 
 * @package Core\Installer
 */
class SetupConfigurationStep extends InstallerStep{
	public function execute(){
		
		$tpl = $this->getTemplate();
		$this->title = 'Configuration';

		// If there's already a configuration file present... just skip to the next.
		/*if(file_exists(ROOT_PDIR . '/config/configuration.xml')){
			$this->setAsPassed();
			reload();
		}*/

		// This will contain the temporary configuration values for the installer.
		if(!isset($_SESSION['configs'])) $_SESSION['configs'] = [];

		if(file_exists(ROOT_PDIR . 'config/configuration.example.xml') && is_readable(ROOT_PDIR . 'config/configuration.example.xml')){
			$xml = new \XMLLoader();
			$xml->setRootName('configuration');
			$xml->loadFromFile(ROOT_PDIR . 'config/configuration.example.xml');
			$elements = $xml->getElements('return|define');
		}
		else{
			$tpl->assign('message', 'Unable to load ' . ROOT_PDIR . 'config/configuration.example.xml!  Please ensure that ' . exec('whoami') . ' has access to that directory.');
			return;
		}
		
		if(file_exists(ROOT_PDIR . 'config/configuration.xml')){
			$tpl->assign(
				'message', 
				'configuration.xml is installed and ready!  If you would like to reconfigure the site, please edit that file manually or remove it altogether.  Otherwise, press Next to continue.'
			);
			$tpl->assign('message_type', 'success');
			
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				reload($this->stepCurrent + 1);
			}
			
			return;
		}
		
		$formelements = [];
		
		// Use Bacon ipsum to generate a secret passphrase for the user.
		$bacon = new \BaconIpsumGenerator();
		$baconWords = $bacon->getWord(rand(8,12));
		// Remove spaces
		$baconWords = str_replace(' ', '', $baconWords);
		
		// Manipulate the string a bit to add some complexity.
		for($i = 0; $i < strlen($baconWords); $i++){
			$change = rand(0, 50);
			if($change < 30){
				continue;
			}
			elseif($change < 45){
				$baconWords{$i} = strtoupper($baconWords{$i});
			}
			elseif($change < 47){
				$set = ['!', '@', '#', '$', '%', '^', '&', '&', '*', '(', ')', '{', '}', '[', ']', '?', '~'];
				$baconWords{$i} = $set[ rand(0, sizeof($set) - 1) ];
			}
			else{
				$set = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
				$baconWords{$i} = $set[ rand(0, sizeof($set) - 1) ];
			}
		}

		// Since we're pulling from the ant version, set some nice defaults for the user.
		$valuedefaults = [
			'database_server' => [
				'template' => '@{db.server}@',
				'default' => 'localhost',
			],
			'database_port' => [
				'template' => '@{db.port}@',
				'default' => '3306',
			],
			'database_type' => [
				'template' => '@{db.type}@',
				'default' => 'mysqli',
			],
			'database_name' => [
				'template' => '@{db.name}@',
				'default' => 'localhost',
			],
			'database_user' => [
				'template' => '@{db.user}@',
				'default' => 'localhost',
			],
			'database_pass' => [
				'template' => '@{db.pass}@',
				'default' => 'localhost',
			],
			'SERVER_ID' => [
				'template' => 'RANDOM',
				'default' => \Core\random_hex(32),
			],
			'DEVELOPMENT_MODE' => [
				'template' => '@{devmode}@',
				'default' => 'false',
			],
			'tmp_dir_web' => [
				'template' => '/tmp/coreplus-web/',
				'default' => '/tmp/' . $_SERVER['HTTP_HOST'] . '-web/',
			],
			'tmp_dir_cli' => [
				'template' => '/tmp/coreplus-cli/',
				'default' => '/tmp/' . $_SERVER['HTTP_HOST'] . '-cli/',
			],
			'SECRET_ENCRYPTION_PASSPHRASE' => [
				'template' => 'RANDOM',
				'default' => $baconWords,
			],
		];

		
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
			if(isset($valuedefaults[$name]) && $value == $valuedefaults[$name]['template']){
				$value = $valuedefaults[$name]['default'];
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
		
		// Assign these elements to the template.
		$tpl->assign('formelements', $formelements);


		// If it's a POST... try the settings and if valid, proceed.
		$message = null;
		$instructions = null;

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$connectionresults = $this->testDatabaseConnection();
			if($connectionresults['status'] != 'passed'){
				//var_dump($connectionresults); die();
				$message = $connectionresults['message'];
				$instructions = $connectionresults['instructions'];
			}


			if($message === null){
				// Still null after all the tests have ran?
				// w00t!
				//$this->setAsPassed();
				reload($this->stepCurrent + 1);
			}
		}

		$tpl->assign('message', $message);
		$tpl->assign('instructions', $instructions);
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
}
