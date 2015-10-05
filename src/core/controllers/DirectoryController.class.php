<?php
// @todo 2012.05.11 cpowell - Can I kill this file?  It doesn't seem to be doing anything.
/**
 * DESCRIPTION
 *
 * @package
 * @since 0.1
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

class DirectoryController extends Controller {
	public static function Index(View $page) {
		$dir = $page->getParameter('directory');
		if (!$dir) {
			$page->error = View::ERROR_BADREQUEST;
			return;
		}

		////// Security checks...

		// Usage of '..' is explicitly denied, as it can escape the filesystem.
		if (strpos($dir, '../') !== false) {
			$page->error = View::ERROR_BADREQUEST;
			return;
		}

		// Directory must contain at least one directory in.
		// And it also must start with public/
		if (!preg_match('/^public\/[a-z0-9]+/', $dir)) {
			$page->error = View::ERROR_BADREQUEST;
			return;
		}

		// Now I can finally start the actual logic.
		$d = Core::Directory($dir);
		if (!$d->isReadable()) {
			$page->error = View::ERROR_NOTFOUND;
			return;
		}

		$page->assign('files', $d->ls());
	}
}
