<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
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
	public function __construct() {
		$this->setRootName('repo');
		$this->load();
	}

	public function addPackage(PackageXML $package) {
		$node    = $package->getPackageDOM();
		$newnode = $this->getDOM()->importNode($node, true);
		$this->getRootDOM()->appendChild($newnode);
	}

	public function write() {
		//return $this->asPrettyXML();
		return $this->asMinifiedXML();
	}

	public function getPackages() {
		$pkgs = array();
		foreach ($this->getElements('package') as $p) {
			$pkg = new PackageXML(null);
			$pkg->loadFromNode($p);
			$pkgs[] = $pkg;
		}
		return $pkgs;
	}
}