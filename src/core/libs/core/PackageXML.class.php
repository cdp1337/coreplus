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
		$this->setType('component');
		$this->setName($component->getName());
		$this->setVersion($component->getVersion());
		$this->setPackager(Core::GetComponent()->getVersion());

		// Copy over any provide directives.
		foreach ($component->getRootDOM()->getElementsByTagName('provide') as $u) {
			$this->getRootDOM()->appendChild($this->getDOM()->importNode($u));
		}
		
		$this->setProvide('component', $component->getName(), $component->getVersion());
	
		// Copy over any requires directives.
		foreach ($component->getRootDOM()->getElementsByTagName('require') as $u) {
			$this->getRootDOM()->appendChild($this->getDOM()->importNode($u));
		}
	
		// Copy over any upgrade directives.
		// This one can be useful for an existing installation to see if this
		// package can provide a valid upgrade path.
		foreach ($component->getRootDOM()->getElementsByTagName('upgrade') as $u) {
			// In this case, I just need the definition itself, I don't also need the contents of that upgrade.
			$this->setUpgrade($u->getAttribute('from'), $u->getAttribute('to'));
		}
	
		// Tack on description
		$desc = $component->getRootDOM()->getElementsByTagName('description')->item(0);
		if ($desc) {
			$this->setDescription($desc->nodeValue);
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

	/**
	 * Get the type of this package, either "component", "theme", or "core"
	 * @return string
	 */
	public function getType() {
		return $this->getRootDOM()->getAttribute('type');
	}

	/**
	 * Get the name translated to a valid keyname for this package
	 * 
	 * @return string
	 */
	public function getKeyName(){
		if($this->getType() == 'core'){
			return 'core';
		}
		else{
			return strtolower(str_replace(' ', '-', $this->getRootDOM()->getAttribute('name')));	
		}
	}

	/**
	 * Get the unmodified name for this package
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->getRootDOM()->getAttribute('name');
	}

	/**
	 * Get the version for this package
	 * 
	 * @return string
	 */
	public function getVersion() {
		return $this->getRootDOM()->getAttribute('version');
	}

	/**
	 * Get the description for this package
	 * 
	 * @return string
	 */
	public function getDescription() {
		return trim($this->getElement('description')->nodeValue);
	}

	/**
	 * Get the file location to download this package
	 * 
	 * @return string
	 */
	public function getFileLocation() {
		return trim($this->getElement('location')->nodeValue);
	}

	/**
	 * Get the packager version for this package
	 * 
	 * @return string
	 */
	public function getPackager(){
		// Most packages will have it as a root-level attribute.
		if($this->getRootDOM()->hasAttribute('packager')){
			return $this->getRootDOM()->getAttribute('packager');
		}
		
		// Others may have an element inside the root node called packager.
		$p = $this->getRootDOM()->getElementsByTagName('packager');
		if($p->length == 1){
			return trim($p->item(0)->attributes->getNamedItem('version')->value);
		}
		
		return '';
	}

	/**
	 * Get an array of requires from this package, with the keys 'name', 'type', 'version', 'operation'.
	 * 
	 * @return array
	 */
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

	/**
	 * Get an array of provides from this package, with the keys 'name', 'type', 'version'.
	 * 
	 * @return array
	 */
	public function getProvides() {
		$ret = array();
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

	/**
	 * Get an array of upgrades from this component, with they keys 'from', and 'to'.
	 * 
	 * @return array
	 */
	public function getUpgrades() {
		$ret = array();
		foreach ($this->getElements('upgrade') as $el) {
			$ret[] = [
				'from' => $el->getAttribute('from'),
				'to'   => $el->getAttribute('to'),
			];
		}
		return $ret;
	}

	/**
	 * If this package is embedded in a repo.xml, it probably has a GPG key associated to it!
	 *
	 * @return string
	 */
	public function getKey(){
		$k = $this->getRootDOM()->getAttribute('key');
		return $k ? preg_replace('/[^a-fA-F0-9]/', '', $k) : null;
	}

	/**
	 * Set the file location for this package to a fully resolved URL.
	 * 
	 * @param string $loc
	 */
	public function setFileLocation($loc) {
		$node            = $this->getElement('location');
		$node->nodeValue = $loc;
	}

	/**
	 * Set the type for this package, probably "component", "theme", or "core".
	 * 
	 * @param $type string
	 */
	public function setType($type) {
		$this->getRootDOM()->setAttribute('type', $type);
	}

	/**
	 * Set the name for this package
	 * 
	 * @param $name string
	 */
	public function setName($name) {
		$this->getRootDOM()->setAttribute('name', $name);
	}

	/**
	 * Set the version for this package
	 *
	 * @param $version string
	 */
	public function setVersion($version) {
		$this->getRootDOM()->setAttribute('version', $version);
	}

	/**
	 * Set the original packager version for this package
	 *
	 * @param $version string
	 */
	public function setPackager($version) {
		$this->getRootDOM()->setAttribute('packager', $version);
	}

	/**
	 * Set a provide line in this package XML
	 * 
	 * @param $type
	 * @param $name
	 * @param $version
	 */
	public function setProvide($type, $name, $version){
		$el = $this->getElement('/provide[name="' . str_replace(' ', '-', strtolower($name)) . '"][type="' . $type . '"]');
		$el->setAttribute('version', $version);
	}

	/**
	 * Set a provide line in this package XML
	 *
	 * @param $type
	 * @param $name
	 * @param $version
	 * @param $op
	 */
	public function setRequire($type, $name, $version = null, $op = null){
		$el = $this->getElement('/require[name="' . str_replace(' ', '-', strtolower($name)) . '"][type="' . $type . '"]');
		
		if($version !== null){
			$el->setAttribute('version', $version);	
		}
		
		if($op !== null){
			$el->setAttribute('operation', $op);
		}
	}

	/**
	 * Set an upgrade path in this package
	 * 
	 * @param $from
	 * @param $to
	 */
	public function setUpgrade($from, $to){
		$this->getElement('/upgrade[from="' . $from . '"][to="' . $to . '"]');
	}

	/**
	 * Set a screenshot for this package
	 *
	 * @param string $url
	 */
	public function setScreenshot($url){
		$node = $this->createElement('/screenshot');
		$node->nodeValue = $url;
	}

	/**
	 * Set the description for this package
	 * 
	 * @param $desc string
	 */
	public function setDescription($desc) {
		$el = $this->getElement('/description');
		$el->nodeValue = $desc;
	}

	/**
	 * Set the GPG key for this package
	 * 
	 * @param $key string
	 */
	public function setKey($key){
		$this->getRootDOM()->setAttribute('key', $key);
	}
	
	public function setChangelog($text){
		$this->getElement('/changelog')->textContent = $text;
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
}