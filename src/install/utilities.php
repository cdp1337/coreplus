<?php
/**
 * File for the various utilities used throughout the installer.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130320.2026
 * @package Core\Installer
 */

namespace Core\Installer;

function reload() {
	// Make the browser do something different so that it doesn't just ignore this and
	// prompt the user with a POST warning on refreshing.
	$date = date('U');

	header('X-Content-Encoded-By: Core Plus');
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location:' . CUR_CALL . '?' . $date);
	die("If your browser does not refresh, please <a href=\"" . CUR_CALL . '?' . $date . "\">Click Here</a>");
}