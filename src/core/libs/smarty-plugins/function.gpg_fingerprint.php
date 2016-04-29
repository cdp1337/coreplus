<?php
/**
 * Smarty plugin
 *
 * @package Core
 * @subpackage PluginsModifier
 */

/**
 * Format a GPG fingerprint into pretty print!
 * 
 * This gives more options than the inline modifier provides.
 *
 * Type:     function<br>
 * Name:     gpg_fingerprint<br>
 * 
 * Options for parameters are:
 * short     = true|FALSE: Set to true to only include the last 8 characters of the fingerprint.
 * html      = TRUE|false: Set to false to return "\n" instead of "<br/>" and multiline is set to true.
 * multiline = true|FALSE: Set to true to return on two lines instead of one.
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string HTML text
 */
function smarty_function_gpg_fingerprint($params, $smarty) {
	$string = $params[0];
	
	$short     = isset($params['short']) ? $params['short'] : false;
	$assign    = isset($params['assign']) ? $params['assign'] : null;
	$ashtml    = isset($params['html']) ? $params['html'] : true;
	$multiline = isset($params['multiline']) ? $params['multiline'] : false;
	
	$string = \Core\GPG\GPG::FormatFingerprint($string, $ashtml, !$multiline);
	
	if($short){
		// I just want the last 9 characters, (8 strings and one space).
		$string = substr($string, -9);
	}

	return $assign ? $smarty->assign($assign, $string) : $string;
}
