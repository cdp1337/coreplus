<?php
/**
 * File for class PerformInstallStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
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
		require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
		require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
		require_once(ROOT_PDIR . 'core/helpers/UpdaterHelper.class.php');

		// Is the system not installed yet?
		//if(!\Core\DB()->tableExists(DB_PREFIX . 'component')){


		\Core::LoadComponents();

		\ThemeHandler::GetTheme('default')->reinstall();
		\ThemeHandler::GetTheme('base-v2')->reinstall();

		unset($_SESSION['passes']);
		// Yup, that's it!
		// The core system handles all installs automatically.
		\Core::Redirect(ROOT_WDIR);
	}
}
