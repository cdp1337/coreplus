<?php

/**
 * Description of File_gz_contents
 * 
 * Provides useful extra functions that can be done with a GZipped file.
 *
 * @author powellc
 */
class File_tgz_contents implements File_Contents{
	private $_file = null;
	
	public function __construct(File_Backend $file) {
		$this->_file = $file;
	}
	
	public function getContents(){
		return $this->_file->getContents();
	}
	
	/**
	 * Extract this archive to a requested directory.
	 * 
	 * @param string $dst Destination to extract the archive to.
	 * 
	 * @return Directory_local_backend
	 */
	public function extract($destdir){
		// This will ensure that the destdir is properlly resolved.
		$d = Core::Directory($destdir);
		if(!$d->isReadable()) $d->mkdir();
		exec('tar -xzf "' . $this->_file->getLocalFilename() . '" -C "' . $d->getPath() . '"');
		return $d;
	}
	
	public function listfiles(){
		$output = array();
		exec('tar -zf "' . $this->_file->getLocalFilename() . '" --list', $output);
		
		foreach($output as $k => $v){
			// Trim some characters off.
			if(strpos($v, './') === 0) $v = substr($v, 2);
			
			if(!$v) unset($output[$k]);
			else $output[$k] = $v;
		}
		
		return array_values($output);
	}
}

