<?php
/**
 * File for class Template definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130411.1238
 * @package Core\Templates
 */

namespace Core\Templates;


/**
 * Class Template description
 * 
 * @package Core\Templates
 */
abstract class Template {

	private static $_Paths = null;

	/**
	 * @param string $filename Filename of the template
	 * @return TemplateInterface|null
	 */
	public static function Factory($filename){
		// Make sure it's resolved first.
		$resolved = self::ResolveFile($filename);
		
		// File couldn't be located?
		if($resolved === null){
			return null;
		}
		
		// The template backend will depend on the extension.
		$ext = \Core\GetExtensionFromString($resolved);

		switch($ext){
			case 'phtml':
				$template = new Backends\PHTML();
				break;
			case 'tpl':
				$template =  new Backends\Smarty();
				break;
			default:
				$template =  new Backends\Smarty();
				break;
		}

		$template->setFilename($resolved);
		return $template;
	}

	/**
	 * Resolve a filename stub to a fully resolved path.
	 *
	 * @param string $filename Filename to resolve
	 *
	 * @return null|string
	 */
	public static function ResolveFile($filename) {
		if(strpos($filename, ROOT_PDIR) === 0){
			// If the template starts with the root pdir, then it must already be fully resolved!
			return $filename;
		}

		$dirs = self::GetPaths();

		// Trim off the beginning '/' if there is one;  All directories end with a '/'.
		if ($filename{0} == '/'){
			$filename = substr($filename, 1);
		}

		foreach ($dirs as $d) {
			if (file_exists($d . $filename)) return $d . $filename;
		}

		// Nope?
		return null;
	}

	/**
	 * Get an array of all the registered Template paths.
	 *
	 * @return array
	 */
	public static function GetPaths(){
		if(self::$_Paths === null){
			self::RequeryPaths();
		}

		return self::$_Paths;

	}

	public static function RequeryPaths() {
		self::$_Paths = array();

		// Tack on the custom directory.
		// This needs to be before the current theme because it takes precedence.
		self::$_Paths[] = ROOT_PDIR . 'themes/custom/';

		// Tack on the current theme's directory.
		self::$_Paths[] = ROOT_PDIR . 'themes/' . \ConfigHandler::Get('/theme/selected') . '/';

		// Tack on the search directories from the loaded components.
		// Also handle the plugins directory search.
		foreach (\Core::GetComponents() as $c) {
			$d = $c->getViewSearchDir();
			// Add the template directory if it exists.
			if ($d){
				// Make sure it ends wih a '/'.
				if($d{strlen($d)-1} != '/') $d .= '/';
				self::$_Paths[] = $d;
			}
		}
	}
}
