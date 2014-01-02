<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
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

// This is the object to handle all tasks related to package archives created via the packager script.
// It can (ideall), handle standard (tgz) files, and signed/encrypted tgz.asc files.

class InstallArchive {
	const SIGNATURE_NONE    = 0;
	const SIGNATURE_VALID   = 1;
	const SIGNATURE_INVALID = 2;

	private $_file;

	private $_manifestdata;
	private $_signature;
	private $_fileconflicts;
	private $_filelist;

	/**
	 * The constructor takes a File object, filename string, or URL string, and provides easy access for
	 * handling operations on that file.
	 */
	public function __construct($file) {
		if ($file instanceof File) {
			$this->_file = $file;
		}
		else {
			// @todo Add support for URLs.
			$this->_file = \Core\Filestore\Factory::File($file);
		}
	}

	public function hasValidSignature() {
		$sig = $this->checkSignature();
		return ($sig['state'] == InstallArchive::SIGNATURE_VALID);
	}

	public function checkSignature() {
		if (is_null($this->_signature)) {
			// Standard tarballs don't have signatures since they're not signed.
			switch ($this->_file->getMimetype()) {
				case 'application/pgp':
					$this->_signature = $this->_checkGPGSignature();
					break;
				default:
					$this->_signature = array('state' => InstallArchive::SIGNATURE_NONE,
					                          'key'   => null,
					                          'email' => null,
					                          'name'  => null);
					break;
			}
		}

		return $this->_signature;
	}

	private function _checkGPGSignature() {
		$crypt_gpg = GPG::Singleton();
		try {
			list($out) = $crypt_gpg->verifyFile($this->_file->getFilename());
		}
		catch (Exception $e) {
			// Nope... guess it didn't work.
			return array('state' => InstallArchive::SIGNATURE_INVALID,
			             'key'   => null,
			             'email' => null,
			             'name'  => null);
		}

		if (!$out->isValid()) {
			return array('state' => InstallArchive::SIGNATURE_INVALID,
			             'key'   => null,
			             'email' => null,
			             'name'  => null);
		}
		else {
			return array(
				'state' => InstallArchive::SIGNATURE_VALID,
				'key'   => $out->getKeyFingerprint(),
				'email' => $out->getUserId()->getEmail(),
				'name'  => $out->getUserId()->getName()
			);
		}
	}

	/**
	 * Every package tarball should have a manifest file in it... extract that out to get its metadata.
	 */
	public function getManifest() {
		if (is_null($this->_manifestdata)) {
			// Standard tarballs don't have signatures since they're not signed.
			switch ($this->_file->getMimetype()) {
				case 'application/pgp':
					// Dependent on a valid signature.
					if (!$this->hasValidSignature()) {
						$this->_manifestdata = $this->_getManifest(null);
					}
					else {
						$tmpfile = '/tmp/outtarball-manifest.tgz';
						$this->_decryptTo($tmpfile);
						$this->_manifestdata = $this->_getManifest($tmpfile);
						unlink($tmpfile);
					}
					break;
				default:
					$this->_manifestdata = $this->_getManifest($this->_file->getFilename());
					break;
			}
		}

		return $this->_manifestdata;
	}

	private function _getManifest($filename) {
		// First check, if a null filename is sent, that means return a default manifest definition.
		if ($filename === null) {
			// Try to guess from the filename.
			$fn = $this->_file->getFilename();
			if (strpos($fn, '-')) {
				list($name, $version) = explode('-', substr($fn, strrpos($fn, '/') + 1, strrpos($fn, '.')));
			}
			else {
				$name    = substr($fn, strrpos($fn, '.'));
				$version = null;
			}
			return array(
				'Manifest-Version' => '1.0',
				'manifestversion'  => '1.0',
				'Bundle-Type'      => 'unknown',
				'bundletype'       => 'unknown',
				'Bundle-Name'      => $name,
				'bundlename'       => $name,
				'Bundle-Version'   => $version,
				'bundleversion'    => $version
			);
		}
		// tar -xzvf TestFoo-1.0.2.tgz ./META-INF/MANIFEST.MF -O
		exec('tar -xzvf "' . $filename . '" ./META-INF/MANIFEST.MF -O', $output);
		$ret = array();
		foreach ($output as $line) {
			if (strpos($line, ':') === false) continue;
			list($k, $v) = explode(':', $line);
			$ret[trim($k)]                                   = trim($v);
			$ret[trim(strtolower(str_replace('-', '', $k)))] = trim($v);
		}
		return $ret;
	}

	private function _decryptTo($filename) {
		$crypt_gpg = GPG::Singleton();
		$crypt_gpg->decryptFile($this->_file->getFilename(), $filename);
	}

	/**
	 * Get all the filenames in this archive.
	 */
	public function getFilelist() {
		if (is_null($this->_filelist)) {
			switch ($this->_file->getMimetype()) {
				case 'application/pgp':
					$tmpfile = '/tmp/outtarball-filelist.tgz';
					$this->_decryptTo($tmpfile);
					$this->_filelist = $this->_getFilelist($tmpfile);
					unlink($tmpfile);
					break;
				default:
					$this->_filelist = $this->_getFilelist($this->_file->getFilename());
					break;
			}
		}

		return $this->_filelist;
	}

	private function _getFilelist($filename) {
		// Get the manifest so I know its type.
		$man  = $this->getManifest();
		$type = $man['Bundle-Type'];

		exec('tar -tzf "' . $filename . '"', $output);

		$ret = array();

		foreach ($output as $line) {
			// Skip the first "./" line.
			if ($line == './') continue;
			// Skip anything outside the './data' directory.
			if (!preg_match(':\./data:', $line)) continue;
			// Skip directories, those ending with a '/'.
			if (preg_match(':/$:', $line)) continue;
			// There are a few special cases that need ignored as well.
			if ($line == './data/component.xml' && $type == 'component') continue;

			// Trim the prefix off the filename, I need to do this because I need just the base filename.
			$file = str_replace('./data/', '', $line);

			$ret[] = $file;
		}

		return $ret;
	}

	/**
	 * Extract a specific file to a specified directory.
	 */
	public function extractFile($filename, $to = false) {
		if (!$to) $to = $this->getBaseDir();
		//echo "Extracting $filename to $to<br/>";
		// Get the full path to extract the file to.
		$fp  = $to . $filename;
		$fb  = './data/' . $filename;
		$dir = dirname($fp);

		if (!is_dir($dir)) {
			// Create it and ensure writable permissions for the owner.
			exec('mkdir -p "' . $dir . '"');
			exec('chmod a+w "' . $dir . '"');
		}

		if (!is_writable($dir)) {
			throw new Exception('Cannot write to directory ' . $dir);
		}
		if (!is_writable($fp)) {
			throw new Exception('Cannot write to file ' . $fp);
		}

		switch ($this->_file->getMimetype()) {
			case 'application/pgp':
				$file   = '/tmp/outtarball-extractfile.tgz';
				$istemp = true;
				$this->_decryptTo($file);
				break;
			default:
				$file   = $this->_file->getFilename();
				$istemp = false;
				break;
		}


		//echo "Executing " . 'tar -xzvf "' . $file . '" "' . $fb . '" -O > "' . $fp . '"' . "<br/>";
		exec('tar -xzvf "' . $file . '" "' . $fb . '" -O > "' . $fp . '"');

		if ($istemp) {
			unlink($file);
		}
	}

	/**
	 * Check the filesystem for an installed version and get any 'conflicts'
	 * there may be.  Think conflict as in SVN version and not windows 'this file already exists' version.
	 */
	public function getFileConflicts() {
		if (is_null($this->_fileconflicts)) {
			// Get the manifest data for this component... that will depend on how to handle the comparisons.
			$man = $this->getManifest();
			// Get a list of files in the archive.
			$files = $this->getFileList();

			switch ($man['Bundle-Type']) {
				case 'component':
					return $this->_getFileConflictsComponent($files);
					break;
			}
		}

		return $this->_fileconflicts;
	}

	public function getBaseDir() {
		$man = $this->getManifest();
		switch ($man['Bundle-Type']) {
			case 'component':
				return ROOT_PDIR . 'components/' . $man['Bundle-Name'] . '/';
			// @todo Add the rest of the types here.
		}
	}

	private function _getFileConflictsComponent($arrayoffiles) {
		$man       = $this->getManifest();
		$basedir   = $this->getBaseDir();
		$component = ComponentHandler::GetComponent($man['Bundle-Name']);

		$changedfiles = $component->getChangedFiles();

		$ret = array();

		// I need to run through the array of files, because if the user changed
		// a file that's no longer under the package's control... I don't care.
		foreach ($arrayoffiles as $line) {
			// Now I can see if the file is in the array of changed files.
			if (in_array($line, $changedfiles)) $ret[] = $line;
		}

		return $ret;
	}
}
