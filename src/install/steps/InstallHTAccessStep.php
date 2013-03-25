<?php
/**
 * File for class InstallHTAccessStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130320.2033
 * @package Core\Installer
 */

namespace Core\Installer;


/**
 * Class InstallHTAccessStep description
 * 
 * @package Core\Installer
 */
class InstallHTAccessStep extends InstallerStep {
	public function execute(){
		$this->title = '.htaccess Setup';
		$tpl = $this->getTemplate();

		// Try to write the file.  If I can't, display what it needs to be along with instructions.
		// Check the for the presence of the .htaccess file.  I always forget that bastard otherwise.
		if(!file_exists(ROOT_PDIR . '.htaccess')){
			$fdata = file_get_contents(ROOT_PDIR . 'htaccess.ex');
			$fdata = preg_replace('/^([\s]*RewriteBase).*$/m', '$1 ' . ROOT_WDIR, $fdata);

			if(is_writable(ROOT_PDIR)){
				// Just automatically copy it over, (with the necessary tranformations).
				file_put_contents(ROOT_PDIR . '.htaccess', $fdata);
				$this->setAsPassed();
				reload();
				// :)
			}
			else{
				// Display the instructions to the user.
				$tpl->assign('contents', $fdata);
			}
		}
		else{
			$this->setAsPassed();
			reload();
		}
	}
}
