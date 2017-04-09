<?php
/**
 * Catches a 404 error and reloads to a "Getting Started" page instead.
 *
 * Well, that's the plan for it.  I haven't implemented this page in full yet.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */


class GettingStartedController extends Controller_2_1 {
	public function index() {
		$this->setTemplate('/pages/gettingstarted/index.tpl');
		$view = $this->getView();
		
		// Open a request to this URL to something known like /admin.
		// If it does not go through, (404), then AllowOverride may not set to All.
		$rewriteNotAvailable = false;
		$rewriteConfig = null;
		
		$f = new \Core\Filestore\Backends\FileRemote(ROOT_URL . 'admin');
		$status = $f->getStatus();
		if($status == 404){
			$rewriteConfig = $this->_findAllowOverrideNone();
			$rewriteNotAvailable = true;
		}

		// Check and see if there are no users in the system. If so, provide a prompt for creating admin.
		$view->disableCache();
		$view->assign('showusercreate', (UserModel::Count() == 0));
		$view->assign('isadmin', Core::User()->checkAccess('g:admin'));
		$view->assign('rewrite_not_available', $rewriteNotAvailable);
		$view->assign('rewrite_config', $rewriteConfig);

		return $view;
	}
	
	private function _findAllowOverrideNone(){
		// Look for the directive containing this site and inform the user
		// how to fix it to have the correct options.
		$isuser = (strpos(ROOT_PDIR, '/home/') === 0);

		if(is_dir('/etc/apache2')){
			$loc = '/etc/apache2';
			if($isuser && is_dir($loc . '/mods-enabled')){
				$loc .= '/mods-enabled';
			}
		}
		elseif(is_dir('/etc/httpd')){
			$loc = '/etc/httpd';
		}
		else{
			// No common locations... up to the user to figure this out.
			return null;
		}

		// Look for it!
		if($isuser){
			$out = [];
			exec('grep -nR "/home/\*/public_html" ' . $loc . ' | sed \'s@:.*@@\'', $out);
			if(sizeof($out)){
				// Found at least once instance, see if there is the directive I'm looking for there.
				foreach($out as $file){
					if(($match = exec('grep AllowOverride None ' . $file . ' | sed \'s@:.*@@\''))){
						return $match;
					}
				}
			}
		}
		else{
			// Normal directive, should be in here somewhere.
			$checks = explode('/', ROOT_PDIR);
			do{
				$check = implode('/', $checks);
				$out = [];
				exec('grep -nR "' . $check . '" ' . $loc . ' | sed \'s@:.*@@\'', $out);
				if(sizeof($out)){
					// Found at least once instance, see if there is the directive I'm looking for there.
					foreach($out as $file){
						if(($match = exec('grep AllowOverride None ' . $file . ' | sed \'s@:.*@@\''))){
							return $match;
						}
					}
				}
				array_pop($checks);
			} while(sizeof($checks));
		}
		
		return null;
	}

	public static function _HookCatch404(View $view) {
		if (REL_REQUEST_PATH == '/') {
			// Index page was requested! ^_^

			// Switch the view's controller with this one.
			$newcontroller = new self();
			// This will allow the system view to be redirected, since I cannot return anything other than a true/false in hook calls.
			$newcontroller->overwriteView($view);
			$view->baseurl = '/gettingstarted';
			$newcontroller->index();

			// Prevent event propagation!
			return false;
		}
	}
}
