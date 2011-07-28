<?php
/**
 * Smarty {script} block
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @since 2011.05
 */

/**
 * Include a script in the main template
 * 
 * Usage:
 * <pre>
 * // Include jquery on this page.
 * {script name="jquery"}{/script}
 * 
 * // Another way to call javascript libraries
 * {script library="jquery"}{/script}
 * 
 * // Traditional "src" tags work too
 * {script src="http://blah.tld/js/jquery.js"}{/script}
 * 
 * // Because it's a block-type tag... you can also do
 * {script}
 *   // This section is automatically plugged into a <script> tag.
 * {/script}
 * 
 * // Specifying the location of the target rendering area is also allowable.
 * // This is useful for scripts that expect to be at the end of the body tag.
 * {script src="js/mylib/foo.js" location="head"}{/script}
 * {script src="js/mylib/foo.js" location="foot"}{/script}
 * </pre>
 * 
 * 
 * @param type $params
 * @param type $template 
 */
function smarty_block_script($params, $innercontent, $template, &$repeat){
	// A script library name is provided.
	if(isset($params['name'])){
		if(!ComponentHandler::LoadScriptLibrary($params['name'])){
			throw new SmartyException('Unable to load script library ' . $params['name']);
		}
	}
	// I guess using "library" to indicate the desired library would make sense too....
	elseif(isset($params['library'])){
		if(!ComponentHandler::LoadScriptLibrary($params['library'])){
			throw new SmartyException('Unable to load script library ' . $params['library']);
		}
	}
	// Allow {script} tags to be called with the traditional src attribute.
	// These are most common for external resources, like facebook connect or google tools
	// but also useful for any standard asset.
	elseif(isset($params['src'])){
		$loc = (isset($params['location']))? $params['location'] : 'head';
		CurrentPage::AddScript($params['src'], $loc);
	}
	// a script tag can be called with no parameters, it is after all a script tag....
	elseif($innercontent){
		// Does this content have a <script> tag already around it?
		if(strpos($innercontent, '<script') === false){
			$innercontent = '<script type="text/javascript">' . $innercontent . '</script>';
		}
		$loc = (isset($params['location']))? $params['location'] : 'head';
		CurrentPage::AddScript($innercontent, $loc);
	}
}
