<?php
/**
 * File for class PerformInstallStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130325.0336
 * @package Core\Installer
 */

namespace Core\Installer;


/**
 * Class PerformInstallStep description
 * 
 * @package Core\Installer
 */
class PerformInstallStep extends InstallerStep {
	public function execute(){
		require_once(ROOT_PDIR . 'core/bootstrap_preincludes.php');
		require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
		require_once(ROOT_PDIR . 'core/libs/core/datamodel/Schema.php');
		require_once(ROOT_PDIR . 'core/libs/core/cli/CLI.class.php');

		try{
			\Core\Utilities\Logger\Logger::$Logstdout = true;
			\Core::LoadComponents();

			//\ThemeHandler::GetTheme('default')->install();
			\ThemeHandler::GetTheme('base-v3')->install();

			unset($_SESSION['passes']);
			// Yup, that's it!
			// The core system handles all installs automatically.
			die('<a href="' . ROOT_WDIR . '">Continue to Core!</a>');
		}
		catch(\Exception $e){
			$this->getTemplate()->assign('errors', $e->getMessage());
			$this->getTemplate()->assign('component', 'Core Plus');
		}
	}
}
