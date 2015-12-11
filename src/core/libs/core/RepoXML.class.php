<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
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

class RepoXML extends XMLLoader {

	/**
	 * The API version of this repo XML.
	 * Usually 1.0 or 2.4
	 *
	 * @var float
	 */
	public $apiversion;

	/** @var array|null Cache of keys on this repo */
	private $_keys = null;

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
		if($this->_keys !== null){
			// Cache!
			return $this->_keys;
		}

		$gpg         = new \Core\GPG\GPG();
		$this->_keys = [];

		foreach($this->getElements('keys/key') as $k){
			$id    = $k->getAttribute('id');
			$key   = null;
			$local = true;

			// Try to find more info about this key!
			// First step is to assign the key from local data.
			// If that fails, gracefully search remote servers for it.
			if(($key = $gpg->getKey($id)) === null){
				$remoteKeys = $gpg->searchRemoteKeys($id);
				foreach($remoteKeys as $k){
					/** @var \Core\GPG\PublicKey $k */
					if($k->id == $id || $k->id_short == $id){
						$key = $k;
						$local = false;
						break;
					}
				}
			}

			if($key !== null){
				$dat = [
					'key'        => $id,
					'available'  => true,
					'installed'  => $local,
					'fingerprint' => \Core\GPG\GPG::FormatFingerprint($key->fingerprint, false, true),
					'uids'        => [],
				];

				foreach($key->uids as $uid){
					/** @var \Core\GPG\UID $uid */
					if($uid->isValid()){
						$dat['uids'][] = ['name' => $uid->fullname, 'email' => $uid->email];
					}
				}
			}
			else{
				$dat = [
					'key'        => $id,
					'available'  => false,
					'installed'  => false,
					'fingerprint' => '',
					'uids'        => [],
				];
			}

			$this->_keys[] = $dat;
		}
		return $this->_keys;
	}

	/**
	 * Add a key to this repo to be downloaded automatically upon installing.
	 *
	 * @param string $id    The ID of the key
	 * @param string $name  The name, used for reference.
	 * @param string $email The email, used to confirm against the public data upon installing.
	 */
	public function addKey($id, $name, $email){
		$key = $this->getElement('keys/key[id="' . $id . '"]');
		//$key->setAttribute('id', $id);
		$key->setAttribute('name', $name);
		$key->setAttribute('email', $email);
	}

	/**
	 * Check and see if the keys registered herein are available and valid in the public servers.
	 *
	 * @return bool
	 */
	public function validateKeys(){
		$gpg = new Core\GPG\GPG();

		foreach($this->getKeys() as $keyData){
			// The key is not available locally nor on the keyservers.
			if(!$keyData['available']){
				return false;
			}
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