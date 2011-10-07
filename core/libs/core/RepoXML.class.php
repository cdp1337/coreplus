<?php

class RepoXML extends XMLLoader{
	public function __construct(){
		$this->setRootName('repo');
		$this->load();
	}
	
	public function addPackage(PackageXML $package){
		$node = $package->getPackageDOM();
		$newnode = $this->getDOM()->importNode($node, true);
		$this->getRootDOM()->appendChild($newnode);
	}
	
	public function write(){
		//return $this->asPrettyXML();
		return $this->asMinifiedXML();
	}
	
	public function getPackages(){
		$pkgs = array();
		foreach($this->getElements('package') as $p){
			$pkg = new PackageXML(null);
			$pkg->loadFromNode($p);
			$pkgs[] = $pkg;
		}
		return $pkgs;
	}
}