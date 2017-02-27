<?php
/**
 * File for class Utils definition in the coreplus project
 * 
 * @package CodeMirror
 * @author Charlie Powell <charlie@evalagency.com>
 * @author Nick Hinsch <nicholas@evalagency.com>
 * @date 20130509.1449
 * @copyright Copyright (C) 2009-2016  Charlie Powell
 * @license MIT
 */

namespace CodeMirror;


/**
 * A short teaser of what Utils does.
 *
 * More lengthy description of what Utils does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Utils
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package CodeMirror
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
abstract class Utils {
	private static $Dependencies = [
		'htmlmixed' => [ 'css', 'xml', 'javascript' ],
		'php'       => [ 'htmlmixed' ],
		'smarty'    => [ 'php' ],
	];
	
	private static $Aliases = [
		'html' => 'htmlmixed',
		'bash' => 'shell',
		'sh'   => 'shell',
	];
	
	/**
	 * Include the base codemirror dependencies.
	 *
	 * @return bool
	 */
	public static function IncludeCodeMirror() {

		\Core\view()->addScript('assets/libs/codemirror/lib/codemirror.js');
		\Core\view()->addStylesheet('assets/libs/codemirror/lib/codemirror.css');
		\Core\view()->addStylesheet('assets/css/codemirror.css'); // This is one that can get overridden with custom themes.

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
	
	/**
	 * Include a mode, (or an alias of a mode), and return the resolved name of the mode.
	 * 
	 * @param string $mode
	 * @return string
	 */
	public static function IncludeMode($mode){
		// Everything requires the core.
		self::IncludeCodeMirror();
		
		// Allow this mode to be an alias of something.
		if(isset(self::$Aliases[$mode])){
			$mode = self::$Aliases[$mode];
		}
		
		if(isset(self::$Dependencies[$mode])){
			// Handle all dependency resolution.
			foreach(self::$Dependencies[$mode] as $dep){
				self::IncludeMode($dep);
			}
		}
		
		// Now this mode can be included.
		\Core\view()->addScript('libs/codemirror/mode/' . $mode . '/' . $mode . '.js');
		
		return $mode;
	}

	public static function IncludeCSS() {
		self::IncludeMode('css');
		return true;
	}

	public static function IncludeSQL() {
		return self::_IncludeMode('sql');
		self::IncludeMode('sql');
		return true;
	}

	public static function IncludeHTML() {
		self::IncludeMode('htmlmixed');
		return true;
	}

	public static function IncludeHTTP() {
		self::IncludeMode('http');
		return true;
	}

	public static function IncludeJS() {
		self::IncludeMode('javascript');
		return true;
	}

	public static function IncludeMD() {
		self::IncludeMode('markdown');
		return true;
	}

	public static function IncludePHP() {
		self::IncludeMode('php');
		return true;
	}

	public static function IncludeSmarty() {
		self::IncludeMode('smarty');
		return true;
	}

	public static function IncludeShell() {
		self::IncludeMode('shell');
		return true;
	}
}