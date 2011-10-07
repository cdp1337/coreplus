<?php
/**
 * Description of FileContentFactory
 *
 * @author powellc
 */
class FileContentFactory {
	public static function GetFromFile(File_Backend $file){
		switch($file->getMimetype()){
			case 'application/x-gzip':
				return new File_gz_contents($file);
			default:
				return new File_unknown_contents($file);
		}
	}
}

?>
