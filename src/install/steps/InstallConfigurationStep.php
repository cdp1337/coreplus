<?php
/**
 * File for class InstallConfigurationStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130320.2149
 * @package Core\Installer
 */

namespace Core\Installer;


/**
 * Perform the actual installation of the cached configuration variables.
 * 
 * @package Core\Installer
 */
class InstallConfigurationStep extends InstallerStep{
	public function execute(){

		// If it exists and is good, nothing else needs to be done, (other than flush the session data)
		// This is hit if the user has to manually copy in the configuration.xml data.
		if(file_exists(ROOT_PDIR . '/config/configuration.xml')){
			unset($_SESSION['configs']);
			$this->setAsPassed();
			reload();
		}

		// Load in the configuration example, merge in the SESSION data, and apply them or display the code.
		$xml = new \XMLLoader();
		$xml->setRootName('configuration');
		$xml->loadFromFile(ROOT_PDIR . 'config/configuration.example.xml');

		$elements = $xml->getElements('return|define');
		foreach($elements as $el){
			$name        = $el->getAttribute('name');
			$children    = $el->childNodes;

			foreach($children as $c){
				if($c->nodeName == 'value'){
					// This one requires a random string.
					if($name == 'SECRET_ENCRYPTION_PASSPHRASE' && isset($_SESSION['configs'][$name]) && $_SESSION['configs'][$name] == 'RANDOM'){
						$value = \Core\random_hex(96);
						$c->nodeValue = $value;
					}
					// An override is provided, use that and overwrite the xml.
					elseif(isset($_SESSION['configs'][$name])){
						$value = $_SESSION['configs'][$name];
						$c->nodeValue = $value;
					}
				}
			}
		}

		// Try to save this back down.
		$fdata = $xml->asPrettyXML();

		if(is_writable(ROOT_PDIR . '/config')){
			// Just automatically copy it over, (with the necessary tranformations).
			file_put_contents(ROOT_PDIR . 'config/configuration.xml', $fdata);
			unset($_SESSION['configs']);
			$this->setAsPassed();
			reload();
			// :)
		}
		else{
			// Display the instructions to the user.
			$this->getTemplate()->assign('contents', $fdata);
		}
	}
}
