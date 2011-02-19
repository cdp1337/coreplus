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
	
	public function __construct($filename = null) {
		switch(ConfigHandler::GetValue('/core/asset_backend')){
			case 'local': $this->_backend = new File(ROOT_PDIR . 'assets/default/' . $filename); break;
			case 'aws-s3': $this->_backend = new FileAWSS3($filename); break;
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
	public function copyFrom($src){
		return $this->_backend->copyFrom($src);
	}
}

?>
