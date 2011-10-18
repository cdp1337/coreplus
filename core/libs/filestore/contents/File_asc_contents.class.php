<?php

/**
 * Description of File_gz_contents
 * 
 * Provides useful extra functions that can be done with a GZipped file.
 *
 * @author powellc
 */
class File_asc_contents implements File_Contents{
	/**
	 * The original file object
	 * 
	 * @var File_Backend
	 */
	private $_file = null;
	
	public function __construct(File_Backend $file) {
		$this->_file = $file;
	}
	
	public function getContents(){
		return $this->_file->getContents();
	}
	
	/**
	 * Verify the GPG signature of this encrypted/signed file.
	 * 
	 * The public key MUST be installed already, otherwise this check will of 
	 * course return false because it's able to verify it.
	 * 
	 * @return boolean 
	 */
	public function verify(){
		$output = array();
		$result = 1;
		exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --verify "' . $this->_file->getLocalFilename() . '"', $output, $result);
		
		return ($result === 0);
	}
	
	/**
	 * Decrypt the encrypted/signed file and return a valid File_Backend object
	 * 
	 * @return mixed 
	 */
	public function decrypt($dest = false){
		if($dest){
			if(is_a($dest, 'File') || $dest instanceof File_Backend){
				// Don't need to do anything! The object either is a File
				// Or is an implmentation of the File_Backend interface.
			}
			else{
				// Well it should be damnit!....
				$file = $dest;
				
				// Is the destination a directory or filename?
				// If it's a directory just tack on this current file's basename.
				if(substr($file, -1) == '/'){
					$file .= $this->_file->getBaseFilename();
				}
				
				// Drop the .asc extension if it's there.
				if($this->_file->getExtension() == 'asc') $file = substr($file, 0, -4);
				
				$dest = Core::File($file);
			}
			
			// And load up the contents!
			$dest->putContents($this->decrypt());
			
			return $dest;
		}
		else{
			// Extract and return the file contents
			ob_start();
			passthru('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --decrypt "' . $this->_file->getLocalFilename() . '"');
			$content = ob_get_contents();
			ob_end_clean();
			
			return $content;
		}
	}
	
}

