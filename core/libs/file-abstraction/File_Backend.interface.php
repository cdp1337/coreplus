<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

/**
 *
 * @author powellc
 */
interface File_Backend {
	public function __construct($filename = null);
	
	public function getFilesize($formatted = false);
	
	public function getMimetype();
	
	public function getExtension();

	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 * 
	 * @return string | false
	 */
	public function getURL();
	
	
	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = ROOT_PDIR);
	
	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false);
	
	/**
	 * Get the hash for this file.
	 */
	public function getHash();
	
	public function delete();

	public function isImage();
	
	public function isText();
	
	/**
	 * Get if this file can be previewed in the web browser.
	 * 
	 * @return boolean
	 */
	public function isPreviewable();
	

	/**
	 * See if this file is in the requested directory.
	 * 
	 * @param $path string
	 * @return boolean
	 */
	public function inDirectory($path);

	public function identicalTo($otherfile);
	
	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string $dest
	 * @param boolean $overwrite
	 * @return File
	 */
	public function copyTo($dest, $overwrite = false);
	
	/**
	 * The recipient of the copyTo command, should $dest be this object instead of a filename.
	 */
	public function copyFrom($src, $overwrite = false);
	
	public function getContents();
	
	public function putContents($data);
	
	public function exists();
}

?>
