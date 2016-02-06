<?php
/**
 * The package XML handler.
 *
 * This has some specifics for the package.xml files, namely the schema.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
 *
 * Compatible with version 1.0 of the API
 * Compatible with version 2.4 of the API
 */

class PackageXML extends XMLLoader {
	/**
	 * Construct a new PackageXML object, as either a new object or an existing one.
	 *
	 * @param null|string $filename The filename to load or null if creating a new one.
	 */
	public function __construct($filename = null) {
		$this->setRootName('package');
		$this->_schema = 'http://corepl.us/api/2_4/package.dtd';

		if($filename){
			$this->setFilename($filename);
			$this->load();
		}
	}

	/**
	 * Set this packagexml with data from the component.
	 * This is useful in the packager.
	 *
	 * WARNING, this will revert any modifications done to the package.xml file!
	 *
	 * @param Component_2_1 $component
	 *
	 * @return string
	 */
	public function setFromComponent(Component_2_1 $component){
		// Populate the root attributes for this component package.
		$this->getRootDOM()->setAttribute('type', 'component');
		$this->getRootDOM()->setAttribute('name', $component->getName());
		$this->getRootDOM()->setAttribute('version', $component->getVersion());
		// Declare the packager
		$this->getRootDOM()->setAttribute('packager', Core::GetComponent()->getVersion());


		// Copy over any provide directives.
		foreach ($component->getRootDOM()->getElementsByTagName('provide') as $u) {
			$this->getRootDOM()->appendChild($this->getDOM()->importNode($u));
		}

		// And the component provide as well.
		$this->getElement('/provide[type="component"][name="' . strtolower($component->getName()) . '"][version="' . $component->getVersion() . '"]');

	
		// Copy over any requires directives.
		foreach ($component->getRootDOM()->getElementsByTagName('require') as $u) {

			$this->getRootDOM()->appendChild($this->getDOM()->importNode($u));
		}
	
		// Copy over any upgrade directives.
		// This one can be useful for an existing installation to see if this
		// package can provide a valid upgrade path.
		foreach ($component->getRootDOM()->getElementsByTagName('upgrade') as $u) {
			// In this case, I just need the definition itself, I don't also need the contents of that upgrade.
			$this->getElement(
				'/upgrade[from="' . $u->getAttribute('from') . '"][to="' . $u->getAttribute('to') . '"]'
			);
		}
	
		// Tack on description
		$desc = $component->getRootDOM()->getElementsByTagName('description')->item(0);
		if ($desc) {
			$descel = $this->getElement('description');
			$descel->nodeValue = trim($desc->nodeValue);
		}
	}

	/**
	 * Get the root DOM.
	 * ... probably should have been called getRootDOM() now that I think about it.
	 *
	 * @return DOMNode
	 */
	public function getPackageDOM() {
		return $this->getRootDOM();
	}

	public function getType() {
		return $this->getRootDOM()->getAttribute('type');
	}

	public function getName() {
		return $this->getRootDOM()->getAttribute('name');
	}

	public function getVersion() {
		return $this->getRootDOM()->getAttribute('version');
	}

	public function getDescription() {
		return trim($this->getElement('description')->nodeValue);
	}

	public function getFileLocation() {
		return trim($this->getElement('location')->nodeValue);
	}

	public function setFileLocation($loc) {
		$node            = $this->getElement('location');
		$node->nodeValue = $loc;
	}

	/**
	 * Check if this package is already installed.
	 *
	 * @return boolean
	 */
	public function isInstalled() {
		switch($this->getType()){
			case 'core':
			case 'component':
				$c = Core::GetComponent($this->getName());
				return ($c && $c->isInstalled());
			case 'theme':
				$t = ThemeHandler::GetTheme($this->getName());
				return ($t && $t->isInstalled());
		}
		//$n = strtolower($this->getName());

		//default, ie: it didn't find it.
		return false;

	}

	/**
	 * Check if this package is already installed and current (at least as new version installed)
	 *
	 * @return boolean
	 */
	public function isCurrent() {
		switch($this->getType()){
			case 'core':
			case 'component':
				$c = Core::GetComponent($this->getName());
				if (!$c) return false; // Not installed?  Not current.
				return version_compare($c->getVersion(), $this->getVersion(), 'ge');
			case 'theme':
				$t = ThemeHandler::GetTheme($this->getName());
				if (!$t) return false; // Not installed?  Not current.
				return version_compare($t->getVersion(), $this->getVersion(), 'ge');
		}
	}

	public function getRequires() {
		$ret = array();
		foreach ($this->getElements('require') as $el) {
			// <require name="JQuery" type="library" version="1.4" operation="ge"/>
			$ret[] = array(
				'name'      => strtolower($el->getAttribute('name')),
				'type'      => $el->getAttribute('type'),
				'version'   => $el->getAttribute('version'),
				'operation' => $el->getAttribute('operation'),
			);
		}
		return $ret;
	}

	public function getProvides() {
		$ret = array();
		/* (this is now handled in the packager correctly!)
		// This element itself.
		$ret[] = array(
			'name'    => strtolower($this->getName()),
			'type'    => 'component',
			'version' => $this->getVersion()
		);
		*/
		foreach ($this->getElements('provide') as $el) {
			// <requires name="JQuery" type="library" version="1.4" operation="ge"/>
			$ret[] = array(
				'name'    => strtolower($el->getAttribute('name')),
				'type'    => $el->getAttribute('type'),
				'version' => $el->getAttribute('version'),
			);
		}
		return $ret;
	}

	/*
	public function isInstallable(){
		// If it's already up to date, it can't be reinstalled.
		if($this->isCurrent()) return false;
		
		$c = ComponentHandler::GetComponent($this->getName());
		
		if($this->isInstalled()){
			// It needs to be upgradeable, (ie: in the upgrade path)
			$upel = $this->getElement('upgrade[from="' . $c->getVersion() . '"]', false);
			// Not in the upgrade path, not upgradable.
			if(!$upel) return false;
		}
	}
	*/

	/**
	 * If this package is embedded in a repo.xml, it probably has a GPG key associated to it!
	 *
	 * @return string
	 */
	public function getKey(){
		$k = $this->getRootDOM()->getAttribute('key');
		return $k ? preg_replace('/[^a-fA-F0-9]/', '', $k) : null;
	}
}