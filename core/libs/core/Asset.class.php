<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Asset
 *
 * @author powellc
 */
class Asset implements IFile {
	/**
	 * @var File
	 */
	private $_backend;
	
	public function __construct($filename = null, $set = 'default') {
		switch(ConfigHandler::GetValue('/core/asset_backend')){
			case 'aws-s3':
				// Trim off the "assets" from the filename, it'll get a new prefix.
				if(strpos($filename, 'assets/') === 0) $filename = substr($filename, 7);
				// Prepend the set.
				$filename = $set . '/' . $filename;
				$this->_backend = new FileAWSS3($filename, ConfigHandler::GetValue('/core/aws/asset_bucket'));
				$this->_backend->storage = AmazonS3::STORAGE_REDUCED; // These are copies of static assets, their reliability really isn't important.
				break;
			default: // "local" is deafult.
			case 'local':
				// Trim off the "assets" from the filename, it'll get a new prefix.
				if(strpos($filename, 'assets/') === 0) $filename = substr($filename, 7);
				$this->_backend = new File(ROOT_PDIR . 'assets/' . $set . '/' . $filename);
				break;
		}
	}
	
	public function getFilesize($formatted = false){
		return $this->_backend->getFilesize($formatted);
	}
	
	public function getMimetype(){
		return $this->_backend->getMimetype();
	}
	
	public function getExtension(){
		return $this->_backend->getExtension();
	}

	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 * 
	 * @return string | false
	 */
	public function getURL(){
		return $this->_backend->getURL();
	}
	
	
	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = ROOT_PDIR){
		return $this->_backend->getFilename($prefix);
	}
	
	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false){
		return $this->_backend->getBaseFilename($withoutext);
	}
	
	/**
	 * Get the hash for this file.
	 */
	public function getHash(){
		return $this->_backend->getHash();
	}
	
	public function delete(){
		return $this->_backend->delete();
	}

	public function isImage(){
		return $this->_backend->isImage();
	}
	
	public function isText(){
		return $this->_backend->isText();
	}
	
	/**
	 * Get if this file can be previewed in the web browser.
	 * 
	 * @return boolean
	 */
	public function isPreviewable(){
		return $this->_backend->isPreviewable();
	}
	

	/**
	 * See if this file is in the requested directory.
	 * 
	 * @param $path string
	 * @return boolean
	 */
	public function inDirectory($path){
		return $this->_backend->inDirectory($path);
	}

	public function identicalTo($otherfile){
		return $this->_backend->identicalTo($otherfile);
	}
	
	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string $dest
	 * @param boolean $overwrite
	 * @return File
	 */
	public function copyTo($dest, $overwrite = false){
		return $this->_backend->copyTo($dest, $overwrite);
	}
	
	/**
	 * The recipient of the copyTo command, should $dest be this object instead of a filename.
	 */
	public function copyFrom($src, $overwrite = false){
		return $this->_backend->copyFrom($src, $overwrite);
	}
	
	public function getContents(){
		return $this->_backend->getContents();
	}
	
	public function putContents($data){
		return $this->_backend->putContents($data);
	}
	
	public function exists(){
		return $this->_backend->exists();
	}
	
	
	public static function ResolveURL($file){
		// Maybe it's cached :)
		$keyname = 'asset-resolveurl';
		$keyttl  = (3600 * 12);
		$cachevalue = Core::Cache()->get($keyname, $keyttl);
		
		if(!isset($cachevalue[$file])){
			// Try the theme'd version first.
			$t = ConfigHandler::GetValue('/core/theme');

			$a = new Asset($file, $t);
			if($a->exists()){
				$cachevalue[$file] = $a->getURL();
			}
			else{
				// Doesn't exist?  Just use the default.
				$a = new Asset($file);
				$cachevalue[$file] = $a->getURL();
			}
			
			Core::Cache()->set($keyname, $cachevalue, $keyttl);
		}
		
		// Return the cached value!
		return $cachevalue[$file];
	}
	
}
