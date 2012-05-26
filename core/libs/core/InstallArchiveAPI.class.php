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

/**
 * A generic class that facilitates all the functions of the InstallArchive system.
 */
abstract class InstallArchiveAPI extends XMLLoader {
	const TYPE_COMPONENT = 'component';
	const TYPE_LIBRARY   = 'library';
	const TYPE_THEME     = 'theme';
	/**
	 * The name of the component.
	 * Has to be unique, (because the name is a directory in /components)
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Version of the component, (propagates to libraries and modules).
	 *
	 * @var string
	 */
	protected $_version;

	/**
	 * Description of this library.
	 * As set from the XML file.
	 *
	 * @var string
	 */
	protected $_description;

	/**
	 * Any update sites provided in this library.
	 *
	 * @var array <<string>>
	 */
	protected $_updateSites = array();

	/**
	 * Array of any authors for the library.
	 * Each element is composed of an array of name, email and url.
	 *
	 * @var array <<array>>
	 */
	protected $_authors = array();

	/**
	 * The iterator for this object, kept as a cache.
	 * @var CAEDirectoryIterator
	 */
	protected $_iterator;

	/**
	 * The type of this InstallArchive type...
	 * @var string $_type
	 */
	protected $_type;

	public function load() {
		$XMLFilename = $this->getXMLFilename();

		// Can't read the file? nothing to load...
		if (!is_readable($XMLFilename)) {
			throw new Exception('Unable to open XML Metafile [' . $XMLFilename . '] for reading.');
		}

		// Start the load procedure.
		$this->setFilename($XMLFilename);
		$this->setRootName($this->_type);


		if (!parent::load()) {
			throw new Exception('Parsing of XML Metafile [' . $XMLFilename . '] failed, not valid XML.');
		}

		if (strtolower($this->getRootDOM()->getAttribute("name")) != strtolower($this->_name)) {
			throw new Exception('Name mismatch in XML Metafile [' . $XMLFilename . '], defined name does not match expected name.');
		}

		$this->_version = $this->getRootDOM()->getAttribute("version");
	}

	public function getRequires() {
		$ret = array();
		foreach ($this->getRootDOM()->getElementsByTagName('requires') as $r) {
			$t  = $r->getAttribute('type');
			$n  = $r->getAttribute('name');
			$v  = @$r->getAttribute('version');
			$op = @$r->getAttribute('operation');
			//$value = @$r->getAttribute('value');

			// Defaults.
			if ($v == '') $v = false;
			if ($op == '') $op = 'ge';

			$ret[] = array(
				'type'      => strtolower($t),
				'name'      => $n,
				'version'   => strtolower($v),
				'operation' => strtolower($op),
				//'value' => $value,
			);
		}
		return $ret;
	}

	public function getDescription() {
		if (is_null($this->_description)) $this->_description = $this->getElement('//description')->nodeValue;

		return $this->_description;
	}

	public function setDescription($desc) {
		// Set the cache first.
		$this->_description = $desc;
		// And set the data in the original DOM.
		$this->getElement('//description')->nodeValue = $desc;
	}

	/**
	 * Set the packager aka packager author.
	 *
	 * (packager is a bit too ambigious in this context).
	 *
	 * @param string $name
	 * @param string $email
	 */
	public function setPackageMaintainer($name, $email) {
		$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/date')->nodeValue = Time::GetCurrent(Time::TIMEZONE_GMT, 'r');
		$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/maintainer[@name="' . $name . '"][@email="' . $email . '"]');
		$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/packager')->nodeValue = 'CAE2 ' . ComponentHandler::GetComponent('core')->getVersion();
	}

	public function getChangelog($version = false) {
		if (!$version) $version = $this->getVersion();
		return $this->getElement('/changelog[@version="' . $version . '"]/notes')->nodeValue;
	}

	public function setChangelog($text, $version = false) {
		if (!$version) $version = $this->getVersion();
		$this->getElement('/changelog[@version="' . $version . '"]/notes')->nodeValue = $text;
	}

	/**
	 * Get the filename of the XML metafile.
	 */
	public function getXMLFilename($prefix = ROOT_PDIR) {
		switch ($this->_type) {
			case InstallArchiveAPI::TYPE_COMPONENT:
				if ($this->_name == 'core') return $prefix . 'core/' . 'component.xml';
				else return $prefix . 'components/' . $this->_name . '/' . 'component.xml';
				break;
			case InstallArchiveAPI::TYPE_LIBRARY:
				return $prefix . 'libraries/' . $this->_name . '/' . 'library.xml';
				break;
			case InstallArchiveAPI::TYPE_THEME:
				return $prefix . 'themes/' . $this->_name . '/' . 'theme.xml';
				break;
		}
	}

	public function getBaseDir($prefix = ROOT_PDIR) {
		switch ($this->_type) {
			case InstallArchiveAPI::TYPE_COMPONENT:
				if ($this->_name == 'core') return $prefix;
				else return $prefix . 'components/' . $this->_name . '/';
				break;
			case InstallArchiveAPI::TYPE_LIBRARY:
				return $prefix . 'libraries/' . $this->_name . '/';
				break;
			case InstallArchiveAPI::TYPE_THEME:
				return $prefix . 'themes/' . $this->_name . '/';
				break;
		}

	}

	/**
	 * Return an array of filenames that have been changed in this component.
	 *
	 * @return array
	 */
	public function getChangedFiles() {
		$ret = array();
		// Run through each file listed under '<file>' and get its md5sum.
		foreach ($this->getElementsByTagName('file') as $node) {
			if (!($filename = @$node->getAttribute('filename'))) continue;
			// Compare the file's md5sum with that saved in the componentXML.
			if ($node->getAttribute('md5') != md5_file($this->getBaseDir() . $filename)) {
				$ret[] = $filename;
			}
		}
		return $ret;
	}

	public function getName() {
		return $this->_name;
	}

	public function getVersion() {
		return $this->_version;
	}

	public function setVersion($vers) {
		if ($vers == $this->_version) return;

		// Switch over any unversioned upgrade directives to this version.
		// First, check just a plain <upgrade> directive.
		if (($upg = $this->getElement('/upgrade[@from=""][@to=""]', false))) {
			// Add the current and dest. attribute to it.
			$upg->setAttribute('from', $this->_version);
			$upg->setAttribute('to', $vers);
		}
		elseif (($upg = $this->getElement('/upgrade[@from="' . $this->_version . '"][@to=""]', false))) {
			$upg->setAttribute('to', $vers);
		}
		else {
			// No node found... just create a new one.
			$newupgrade = $this->getElement('/upgrade[@from="' . $this->_version . '"][@to="' . $vers . '"]');
		}


		// Also switch over any unversioned changelog information to this version.
		$newchangelog = $this->getElement('/changelog[@version="' . $vers . '"]');
		foreach ($this->getElementsByTagName('changelog') as $el) {
			if (!@$el->getAttribute('version')) {
				$newchangelog->nodeValue .= "\n" . $el->nodeValue;
				$el->nodeValue = '';
				break;
			}
		}
		$this->_version = $vers;
		$this->getRootDOM()->setAttribute('version', $vers);
	}

	/**
	 * Get the raw XML of this component, useful for debugging.
	 *
	 * @return string (XML)
	 */
	public function getRawXML() {
		return $this->asPrettyXML();
	}

	/**
	 * Return an array of every license, (and its URL), in this component.
	 */
	public function getLicenses() {
		$ret = array();
		foreach ($this->getRootDOM()->getElementsByTagName('license') as $el) {
			$url   = @$el->getAttribute('url');
			$ret[] = array(
				'title' => $el->nodeValue,
				'url'   => $url
			);
		}
		return $ret;
	}

	public function setLicenses($licenses) {
		// First, remove any licenses currently in the XML.
		$this->removeElements('/license');

		// Now I can add the ones in the licenses array.
		foreach ($licenses as $lic) {
			$str          = '/license' . ((isset($lic['url']) && $lic['url']) ? '[@url="' . $lic['url'] . '"]' : '');
			$l            = $this->getElement($str);
			$l->nodeValue = $lic['title'];
		}
	}

	/**
	 * Return an array of every author in this component.
	 */
	public function getAuthors() {
		$ret = array();
		foreach ($this->getRootDOM()->getElementsByTagName('author') as $el) {
			$ret[] = array(
				'name'  => $el->getAttribute('name'),
				'email' => @$el->getAttribute('email'),
			);
		}
		return $ret;
	}

	public function setAuthors($authors) {
		// First, remove any authors currently in the XML.
		$this->removeElements('/author');

		// Now I can add the ones in the authors array.
		foreach ($authors as $a) {
			if (isset($a['email']) && $a['email']) {
				$this->getElement('//component/author[@name="' . $a['name'] . '"][@email="' . $a['email'] . '"]');
			}
			else {
				$this->getElement('//component/author[@name="' . $a['name'] . '"]');
			}
		}
	}

	/**
	 * Get every registered filename and its hash in this component.
	 */
	public function getAllFilenames() {
		$ret  = array();
		$list = $this->getElements('//component/library/file|//component/module/file|//component/view/file|//component/otherfiles/file|//component/assets/file');
		//foreach($this->getRootDOM()->getElementsByTagName('file') as $el){
		foreach ($list as $el) {
			$md5   = @$el->getAttribute('md5');
			$ret[] = array(
				'file' => $el->getAttribute('filename'),
				'md5'  => $md5
			);
		}
		return $ret;
	}

	/**
	 * Get the directory iterator object of this object, ready to be iterated.
	 *
	 * @return DirectoryCAEIterator
	 */
	public function getDirectoryIterator() {
		if (is_null($this->_iterator)) {
			$this->_iterator = new CAEDirectoryIterator();
			// @todo I imagine the calling function would want every directory, including the view...
			//       this can be ignored manually if neeeded externally.
			// Ignore the view search directory if it has one. (provided via above loop)
			//if($this->hasView()) $this->_iterator->addIgnore($this->getViewSearchDir());
			// Ignore the component metaxml, this will get added automatically via the installer.
			$this->_iterator->addIgnore($this->getXMLFilename());
			// The core has a "few" extra ignores to it...

			if ($this->_name == 'core') {
				$this->_iterator->addIgnores('components/', 'config/', 'dropins/', 'exports/', 'nbproject/', 'scripts/', 'themes/', 'update_site/', 'utils/');
				if (ConfigHandler::Get('/core/filestore/assetdir')) $this->_iterator->addIgnore(ConfigHandler::Get('/core/filestore/assetdir'));
				if (ConfigHandler::Get('/core/filestore/publicdir')) $this->_iterator->addIgnore(ConfigHandler::Get('/core/filestore/publicdir'));
			}

			// If the author set any files to be ignored.. ignore those too.
			$list = $this->getElements('/ignorefiles/file');
			foreach ($list as $el) {
				$this->_iterator->addIgnores($this->getBaseDir() . $el->getAttribute('filename'));
			}

			$this->_iterator->setPath($this->getBaseDir());

			// @todo Should this be done on the cached copy or not?
			//       If additional 'addIgnores' are done, this scan will be invalidated and counter-productive.
			$this->_iterator->scan();
		}

		// Do not return the original object, because the act of using the iterator will change it.
		// It tends to be very schroeder-esque.....  Damn effing cat!
		return clone $this->_iterator;
	}
}
