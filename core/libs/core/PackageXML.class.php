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

class PackageXML extends XMLLoader{
	public function __construct($filename){
		$this->setFilename($filename);
		$this->setRootName('package');
		$this->load();
	}
	
	public function getPackageDOM(){
		return $this->getRootDOM();
	}
	
	public function getType(){
		return $this->getRootDOM()->getAttribute('type');
	}
	
	public function getName(){
		return $this->getRootDOM()->getAttribute('name');
	}
	
	public function getVersion(){
		return $this->getRootDOM()->getAttribute('version');
	}
	
	public function getDescription(){
		// @todo Implement this
		return '';
	}
	
	public function getFileLocation(){
		return $this->getElement('location')->nodeValue;
	}
	
	public function setFileLocation($loc){
		$node = $this->getElement('location');
		$node->nodeValue = $loc;
	}
	
	/**
	 * Check if this package is already installed.
	 * 
	 * @return boolean
	 */
	public function isInstalled(){
		//$n = strtolower($this->getName());
		return (ComponentHandler::GetComponent($this->getName()) );
	}
	
	/**
	 * Check if this package is already installed and current (at least as new version installed)
	 * 
	 * @return boolean 
	 */
	public function isCurrent(){
		$c = ComponentHandler::GetComponent($this->getName());
		
		if(!$c) return false; // Not installed?  Not current.
		
		return version_compare($c->getVersion(), $this->getVersion(), 'ge');
	}
	
	public function getRequires(){
		$ret = array();
		foreach($this->getElements('requires') as $el){
			// <requires name="JQuery" type="library" version="1.4" operation="ge"/>
			$ret[] = array(
				'name' => strtolower($el->getAttribute('name')),
				'type' => $el->getAttribute('type'),
				'version' => $el->getAttribute('version'),
				'operation' => $el->getAttribute('operation'),
			);
		}
		return $ret;
	}
	
	public function getProvides(){
		$ret = array();
		// This element itself.
		$ret[] = array(
			'name' => strtolower($this->getName()),
			'type' => 'component',
			'version' => $this->getVersion()
		);
		foreach($this->getElements('provides') as $el){
			// <requires name="JQuery" type="library" version="1.4" operation="ge"/>
			$ret[] = array(
				'name' => strtolower($el->getAttribute('name')),
				'type' => $el->getAttribute('type'),
				'version' => $el->getAttribute('version'),
				'operation' => $el->getAttribute('operation'),
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
}