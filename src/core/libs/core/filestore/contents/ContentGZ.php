<?php
/**
 * Description of ContentGZ
 *
 * Provides useful extra functions that can be done with a GZipped file.
 *
 * @package
 * @since 0.1
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

namespace Core\Filestore\Contents;

use Core\Filestore;

class ContentGZ implements Filestore\Contents {
	private $_file = null;

	public function __construct(Filestore\File $file) {
		$this->_file = $file;
	}

	public function getContents() {
		return $this->_file->getContents();
	}

	/**
	 * Uncompress this file contents and return the result.
	 * Obviously, if a multi-gigibyte file is read with no immediate destination,
	 * you'll probably run out of memory.
	 *
	 * @param Filestore\File|bool $dst The destination to write the uncompressed data to
	 *        If not provided, just returns the data.
	 *
	 * @return mixed
	 */
	public function uncompress($dst = false) {
		$zd = gzopen($this->_file->getLocalFilename(), "r");
		if (!$zd) return false;

		$contents = '';
		while (!feof($zd)) {
			$contents .= gzread($zd, 2048);
		}
		gzclose($zd);

		if($dst){
			$dst->putContents($contents);
		}
		else{
			return $contents;
		}
	}
}

