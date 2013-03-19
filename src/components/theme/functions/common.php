<?php
/**
 * Common private functions for the Theme component.
 * 
 * These are publically visible, but not necessarily meant to be used externally, (though can be)
 */

namespace Theme;


/**
 * Validate that a requested theme name is valid and exists on the filesystem.
 * 
 * @param string $theme
 * @return bool
 */
function validate_theme_name($theme){
	
	// Static variable to use as application cache.
	static $_cache = array();
	
	// Already checked?
	if(isset($_cache[$theme])) return $_cache[$theme];
	
	// Guess not, set it to false by default.
	$_cache[$theme] = false;
	
	// Ensure that the theme is not empty, doesn't begin with a '.', is in the same directory, and exists.
	if(!$theme) return false;
	if($theme{0} == '.') return false;
	if(strpos($theme, '..') !== false) return false;
	if(!is_dir(\ROOT_PDIR . 'themes/' . $theme)) return false;
	
	// And if it made it here...
	$_cache[$theme] = true;
	return true;
}

/**
 * Validate that a requested theme template combination are valid and exist.
 * 
 * @param string $theme
 * @param string $template 
 */
function validate_template_name($theme, $template){
	if(!validate_theme_name($theme)) return false;
	
	$filename = \ROOT_PDIR . 'themes/' . $theme . '/skins/' . $template;
		
	if($template{0} == '.' || !$template || !is_readable($filename)){
		return false;
	}
	
	return true;
}
