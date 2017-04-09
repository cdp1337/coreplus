<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
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
