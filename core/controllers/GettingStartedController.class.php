<?php
/**
 * Catches a 404 error and reloads to a "Getting Started" page instead.
 *
 * Well, that's the plan for it.  I haven't implemented this page in full yet.
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

		// Check and see if there are no users in the system. If so, provide a prompt for creating admin.
		$view->assign('showusercreate', (UserModel::Count() == 0));
		$view->assign('isadmin', Core::User()->checkAccess('g:admin'));

		return $view;
	}

	public static function _HookCatch404(View $view) {
		if (REL_REQUEST_PATH == '/') {
			// Index page was requested! ^_^

			// Switch the view's controller with this one.
			$newcontroller = new self();
			// This will allow the system view to be redirected, since I cannot return anything other than a true/false in hook calls.
			$newcontroller->overwriteView($view);
			$view->baseurl = '/GettingStarted';
			$newcontroller->index();

			// Prevent event propagation!
			return false;
		}
	}
}

?>
