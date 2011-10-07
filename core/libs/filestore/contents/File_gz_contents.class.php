<?php

/**
 * Description of File_gz_contents
 * 
 * Provides useful extra functions that can be done with a GZipped file.
 *
 * @author powellc
 */
class File_gz_contents implements File_Contents{
	private $_file = null;
	
	public function __construct(File_Backend $file) {
		$this->_file = $file;
	}
	
	public function getContents(){
		return $this->_file->getContents();
	}
	
	/**
	 * Uncompress this file contents and return the result.
	 * Obviously, if a multi-gigibyte file is read with no immediate destination,
	 * you'll probably run out of memory.
	 * 
	 * @param File_Backend|false $dst The destination to write the uncompressed data to
	 *        If not provided, just returns the data.
	 * 
	 * @return mixed
	 */
	public function uncompress($dst = false){
		if($dst){
			// @todo decompress the file to the requested destination file.
		}
		else{
			// Just return the file contents.
			$zd = gzopen($this->_file->getLocalFilename(), "r");
			if(!$zd) return false;
			
			$contents = '';
			while(!feof($zd)){
				$contents .= gzread($zd, 2048);
			}
			gzclose($zd);
			
			return $contents;
		}
	}
}

