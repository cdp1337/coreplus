<?php
/**
 * Description of ContentASC
 *
 * Provides useful extra functions that can be done with a GZipped file.
 *
 * @package
 * @since 0.1
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

namespace Core\Filestore\Contents;

use Core\Filestore;

class ContentASC implements Filestore\Contents {
	/**
	 * The original file object
	 *
	 * @var Filestore\File
	 */
	private $_file = null;

	public function __construct(Filestore\File $file) {
		$this->_file = $file;
	}

	public function getContents() {
		return $this->_file->getContents();
	}

	/**
	 * Verify the GPG signature of this encrypted/signed file.
	 *
	 * The public key MUST be installed already, otherwise this check will of
	 * course return false because it's able to verify it.
	 *
	 * @return boolean
	 */
	public function verify() {
		$output = array();
		$result = 1;
		exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --verify "' . $this->_file->getLocalFilename() . '"', $output, $result);

		return ($result === 0);
	}

	/**
	 * Get the public key that was used to sign this file.
	 *
	 * @return string
	 */
	public function getKey() {
		$output = array();
		$result = 1;
		exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --verify "' . $this->_file->getLocalFilename() . '" 2>&1 | grep "key ID" | sed \'s:.*key ID \([A-Z0-9]*\)$:\1:\'', $output, $result);

		return $output[0];
	}

	/**
	 * Decrypt the encrypted/signed file and return a valid File object
	 *
	 * @return mixed
	 */
	public function decrypt($dest = false) {
		if ($dest) {
			if (is_a($dest, 'File') || $dest instanceof Filestore\File) {
				// Don't need to do anything! The object either is a File
				// Or is an implmentation of the File interface.
			}
			else {
				// Well it should be damnit!....
				$file = $dest;

				// Is the destination a directory or filename?
				// If it's a directory just tack on this current file's basename.
				if (substr($file, -1) == '/') {
					$file .= $this->_file->getBaseFilename();
				}

				// Drop the .asc extension if it's there.
				if ($this->_file->getExtension() == 'asc') $file = substr($file, 0, -4);

				$dest = Filestore\Factory::File($file);
			}

			// And load up the contents!
			$dest->putContents($this->decrypt());

			return $dest;
		}
		else {
			// Extract and return the file contents
			ob_start();
			passthru('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --decrypt "' . $this->_file->getLocalFilename() . '"');
			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}
	}

}

