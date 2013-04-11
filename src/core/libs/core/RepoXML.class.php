<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
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

class RepoXML extends XMLLoader {

	/**
	 * The API version of this repo XML.
	 * Usually 1.0 or 2.4
	 *
	 * @var float
	 */
	public $apiversion;

	/**
	 * An associative array of the keys available in this repo.
	 *
	 * @var array
	 */
	public $keys = array();

	public function __construct($filename = null) {
		$this->setRootName('repo');
		$this->_schema = 'http://corepl.us/api/2_4/repo.dtd';

		if($filename){
			$this->setFilename($filename);
		}
		$this->load();
	}

	/**
	 * Clear the list of packages... useful for the create_repo script.
	 */
	public function clearPackages(){
		$this->removeElements('core');
		$this->removeElements('components');
		$this->removeElements('themes');
	}

	/**
	 * Add a single package to this repo
	 *
	 * @param PackageXML $package
	 */
	public function addPackage(PackageXML $package) {
		// This DOMNode that will be written
		$node    = $package->getPackageDOM();

		// The type, this determines where it'll go.
		if($package->getName() == 'core'){
			// Yeah I know the plural of core is technically cores....
			// <core> sounds better; consider it as "data"!
			$type = 'core';
		}
		else{
			// . s because the repo contains many of "$type" directives.
			$type = $package->getType() . 's';
		}

		$newnode = $this->getDOM()->importNode($node, true);
		$dest = $this->getElement($type);
		$dest->appendChild($newnode);
	}

	/**
	 * Get this repo's description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return trim($this->getElement('description')->nodeValue);
	}

	/**
	 * Set the description for this repo.
	 *
	 * @param $desc
	 */
	public function setDescription($desc){
		$this->getElement('description')->nodeValue = $desc;
	}

	/**
	 * Get an array of keys to install automatically with this repo.
	 *
	 * @return array
	 */
	public function getKeys(){
		$keys = array();
		foreach($this->getElements('keys/key') as $k){
			$keys[] = array(
				'id' => $k->getAttribute('id'),
				'name' => $k->getAttribute('name'),
				'email' => $k->getAttribute('email'),
			);
		}
		return $keys;
	}

	/**
	 * Add a key to this repo to be downloaded automatically upon installing.
	 *
	 * @param $id    The ID of the key
	 * @param $name  The name, used for reference.
	 * @param $email The email, used to confirm against the public data upon installing.
	 */
	public function addKey($id, $name, $email){
		$key = $this->getElement('keys/key');
		$key->setAttribute('id', $id);
		$key->setAttribute('name', $name);
		$key->setAttribute('email', $email);
	}

	/**
	 * Check and see if the keys registered herein are available and valid in the public servers.
	 *
	 * @return bool
	 */
	public function validateKeys(){
		foreach($this->getKeys() as $key){
			// Lookup this key at the registered keyserver.  This is to validate that it's available and everything matches.
			$output = array();
			// Because this will be sent to the command line..... I want to do a bit of cleaning.
			$id = strtoupper(preg_replace('/[^a-zA-Z0-9]*/', '', $key['id']));

			exec('gpg --keyserver-options timeout=6 --homedir "' . GPG_HOMEDIR . '" --no-permission-warning -q --batch --keyserver hkp://pool.sks-keyservers.net --dry-run --recv-keys ' . $id, $output, $result);
			//exec('gpg --keyserver-options timeout=6 --homedir "' . GPG_HOMEDIR . '" --no-permission-warning -q --batch --keyserver hkp://pool.sks-keyservers.net --search-key ' . $id, $output, $result);

			// If a key fails lookup, gpg will exit with a status of 0.
			if($result != 0){
				error_log('Key lookup failed!' . "\n" . implode("\n", $output));
				return false;
			}

			// I also need to check that the email registered is present!
			//if(strpos(implode("", $output), $key['email']) === false){
			//	error_log('Key lookup failed because email address set [' . $key['email'] . '] was not associated with key' . "\n" . implode("\n", $output));
			//	return false;
			//}
		}

		// Did all the keys pass validation?
		return true;
	}

	public function write() {
		//return $this->asPrettyXML();
		return $this->asMinifiedXML();
	}

	public function getPackages() {
		$pkgs = array();
		foreach ($this->getElements('core/package|components/package|themes/package') as $p) {
			$pkg = new PackageXML(null);
			$pkg->loadFromNode($p);
			$pkgs[] = $pkg;
		}
		return $pkgs;
	}
}