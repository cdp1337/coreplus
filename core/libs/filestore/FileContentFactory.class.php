<?php
/**
 * Description of FileContentFactory
 *
 * @author powellc
 */
class FileContentFactory {
	public static function GetFromFile(File_Backend $file) {
		switch ($file->getMimetype()) {
			case 'application/x-gzip':
				// gzip can be a wrapper around a lot of things.  
				// Some of them even have their own content functions.
				if (strtolower($file->getExtension()) == 'tgz') return new File_tgz_contents($file);
				else return new File_gz_contents($file);
			case 'text/plain':
				// Sometimes these are actually other files based on the extension.
				if (strtolower($file->getExtension()) == 'asc') return new File_asc_contents($file);
				else return new File_unknown_contents($file);
			default:
				return new File_unknown_contents($file);
		}
	}
}

?>
