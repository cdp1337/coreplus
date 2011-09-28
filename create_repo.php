#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to create a repository that can be distributed 
 * pulled from remote servers.
 * 
 * @package Core
 * @since 2011.09
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}


// Inlude the core bootstrap, this will get the system functional.
require_once('core/bootstrap.php');


// I need a valid editor.
CLI::RequireEditor();

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
}

class PackageXML extends XMLLoader{
	public function __construct($filename){
		$this->setFilename($filename);
		$this->setRootName('package');
		$this->load();
	}
	
	public function getPackageDOM(){
		return $this->getRootDOM();
	}
}

$destdir = ROOT_PDIR . 'exports/';
$tmpdir = ROOT_PDIR . 'exports/_tmp/';
// Ensure the export directory exists.
if(!is_dir($destdir)) exec('mkdir -p "' . $destdir . '"');
if(!is_dir($tmpdir)) exec('mkdir -p "' . $tmpdir . '"');

$repo = new RepoXML();
$addedpackages = 0;
$failedpackages = 0;


// Load in all valid components in the exports/components directory.
$dh = opendir($destdir . 'components');
$output = array();
$ret = null;
if($dh){
	while(($file = readdir($dh)) !== false){
		$fullpath = $destdir . 'components/' . $file;
		
		// Skip hidden files
		if($file{0} == '.') continue;
		
		// Only package up ASC files.
		if(!preg_match('/\.asc$/i', $file)) continue;
		
		// Drop the .asc extension.
		$basename = substr($file, 0, -4);
		
		echo NL . NL . "Processing component " . $file . "..." . NL;
		
		// decode and untar it in a temp directory to get the package.xml file.
		exec('gpg -d "' . $fullpath . '" > "' . $tmpdir . $basename . '"', $output, $ret);
		if($ret){
			echo "FAILED to decrypt file!" . NL;
			$failedpackages++;
			continue;
		}
		
		exec('tar -xzf "' . $tmpdir . $basename . '" -C "' . $tmpdir . '" ./package.xml', $output, $ret);
		if($ret){
			echo "FAILED to extract package.xml!" . NL;
			unlink($tmpdir . $basename);
			$failedpackages++;
			continue;
		}
		
		// Read in that package file and append it to the repo xml.
		$package = new PackageXML($tmpdir . 'package.xml');
		$repo->addPackage($package);
		$addedpackages++;
		
		// Cleanup!
		unlink($tmpdir . 'package.xml');
		unlink($tmpdir . $basename);
		
		echo "Added package!" . NL;
	}
}

file_put_contents($destdir . 'repo.xml', $repo->write());
// And gzip!
exec('gzip "' . $destdir . 'repo.xml' . '"');

rmdir($tmpdir);

echo NL . NL . "Created " . $destdir . 'repo.xml.gz successfully' . NL . 'Packages Added: ' . $addedpackages . NL . 'Packages Failed: ' . $failedpackages . NL;
