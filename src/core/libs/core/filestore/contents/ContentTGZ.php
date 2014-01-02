<?php
/**
 * Description of ContentTGZ
 *
 * Provides useful extra functions that can be done with a GZipped file.
 *
 * @package
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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


namespace Core\Filestore\Contents;

use Core\Filestore;

class ContentTGZ implements Filestore\Contents {
	private $_file = null;

	public function __construct(Filestore\File $file) {
		$this->_file = $file;
	}

	public function getContents() {
		return $this->_file->getContents();
	}

	/**
	 * Extract this archive to a requested directory.
	 *
	 * @param string $dst Destination to extract the archive to.
	 *
	 * @return \Core\Filestore\Directory
	 */
	public function extract($destdir) {
		// This will ensure that the destdir is properlly resolved.
		$d = \Core::Directory($destdir);
		if (!$d->isReadable()) $d->mkdir();
		exec('tar -xzf "' . $this->_file->getLocalFilename() . '" -C "' . $d->getPath() . '"');
		return $d;
	}

	public function listfiles() {
		$output = array();
		exec('tar -zf "' . $this->_file->getLocalFilename() . '" --list', $output);

		foreach ($output as $k => $v) {
			// Trim some characters off.
			if (strpos($v, './') === 0) $v = substr($v, 2);

			if (!$v) unset($output[$k]);
			else $output[$k] = $v;
		}

		return array_values($output);
	}
}

