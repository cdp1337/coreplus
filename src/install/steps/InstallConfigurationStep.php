<?php
/**
 * File for class InstallConfigurationStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130320.2149
 * @package Core\Installer
 */

namespace Core\Installer;
use Core\Session;


/**
 * Perform the actual installation of the cached configuration variables.
 * 
 * @package Core\Installer
 */
class InstallConfigurationStep extends InstallerStep{
	public function execute(){
		
		$this->title = 'Deploy Configuration';
		$tpl = $this->getTemplate();

		// If it exists and is good, nothing else needs to be done, (other than flush the session data)
		// This is hit if the user has to manually copy in the configuration.xml data.
		/*if(file_exists(ROOT_PDIR . '/config/configuration.xml')){
			unset($_SESSION['configs']);
			$this->setAsPassed();
			reload();
		}*/
		
		// Load in the configuration example, merge in the SESSION data, and apply them or display the code.
		$xml = new \XMLLoader();
		$xml->setRootName('configuration');
		$xml->loadFromFile(ROOT_PDIR . 'config/configuration.example.xml');
		$elements = $xml->getElements('return|define');
		foreach($elements as $el){
			$name        = $el->getAttribute('name');
			$children    = $el->childNodes;

			foreach($children as $c){
				// Iterate through each child node of this return or define looking for the 'value' node.
				if($c->nodeName == 'value' && isset($_SESSION['configs'][$name])){
					// An override from the user is available for this value, use that instead!
					$c->nodeValue = $_SESSION['configs'][$name];
				}
			}
		}

		$fdata = $xml->asPrettyXML();
		
		if(file_exists(ROOT_PDIR . '/config/configuration.xml')){
			$tpl->assign('message', 'configuration.xml has already been deployed!  Click next to continue.');
		}
		elseif(is_writable(ROOT_PDIR . '/config')){
			// Just automatically copy it over, (with the necessary tranformations).
			file_put_contents(ROOT_PDIR . 'config/configuration.xml', $fdata);
			unset($_SESSION['configs']);
			$tpl->assign('message', 'configuration.xml was automatically deployed with the configured parameters!  Click next to continue.');
		}
		else{
			// Display the instructions to the user.
			$tpl->assign('contents', $fdata);
		}
	}
}
