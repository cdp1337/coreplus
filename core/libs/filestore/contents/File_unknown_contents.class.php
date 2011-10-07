<?php
/**
 * Description of File_unknown_contents
 *
 * @author powellc
 */
class File_unknown_contents implements File_Contents{
	private $_file = null;
	
	public function __construct(File_Backend $file) {
		$this->_file = $file;
	}
	
	public function getContents(){
		return $this->_file->getContents();
	}
}

?>
