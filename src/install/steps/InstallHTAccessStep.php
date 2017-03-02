<?php
/**
 * File for class InstallHTAccessStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
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
		
		$e = file_exists(ROOT_PDIR . '.htaccess');
		$r = ($e && is_readable(ROOT_PDIR . '.htaccess'));
		$w = is_writable(ROOT_PDIR);
		
		// Load the example version and perform the necessary string replacements.
		$fdata = file_get_contents(ROOT_PDIR . 'htaccess.example');
		$replaces = [
			'@{rewritebase}@' => ROOT_WDIR,
			'@{build.time}@' => date('r'),
		];
		$fdata = str_replace(array_keys($replaces), array_values($replaces), $fdata);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			// user clicked "next"
			if(!$e && $w){
				file_put_contents(ROOT_PDIR . '.htaccess', $fdata);
				$this->setAsPassed();
				reload($this->stepCurrent + 1);
			}
			elseif($e){
				$this->setAsPassed();
				reload($this->stepCurrent + 1);
			}
		}
		
		if($e && $r){
			// Exists AND readable; inform the user that everything is ready and continue on.
			$tpl->assign('status', 'good');
			return;
		}
		elseif($e && !$r){
			// Exists but is not readable.  This is a strange fringe case.
			$tpl->assign('status', 'error');
			$tpl->assign('message', 'There seems to be a problem with the permissions on ' . ROOT_PDIR . '.htaccess .  Please check that ' . exec('whoami') . ' can access that file.');
		}
		elseif(!$e && $w){
			// Does not exist, but is writable.
			$tpl->assign('status', 'good');
		}
		else{
			// Doesn't exist and not writable, or some other weird scenario.
			$tpl->assign('status', 'error');
			$tpl->assign('contents', $fdata);
		}
	}
}
