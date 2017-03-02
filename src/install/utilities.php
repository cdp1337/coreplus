<?php
/**
 * File for the various utilities used throughout the installer.
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130320.2026
 * @package Core\Installer
 */

namespace Core\Installer;

function reload($step = null) {
	// Make the browser do something different so that it doesn't just ignore this and
	// prompt the user with a POST warning on refreshing.
	$date = date('U');
	
	$path = CUR_CALL;
	// Append the step if requested.
	if($step){
		$path .= '?step=' . $step;
	}
	else{
		$path .= '?step=';
	}
	
	// And teh date.
	$path .= '&d=' . $date;

	header('X-Content-Encoded-By: Core Plus');
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location:' . $path);
	die("If your browser does not refresh, please <a href=\"" . $path . "\">Click Here</a>");
}