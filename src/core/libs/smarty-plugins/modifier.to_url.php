<?php
/**
 * Smarty plugin
 *
 * @package Core
 * @subpackage PluginsModifier
 */

/**
 * Execute the Core str_to_url function and return the result.
 *
 * Type:     modifier<br>
 * Name:     to_url<br>
 *
 * @param string  $string input string
 * @return string URLified string
 */
function smarty_modifier_to_url($string) {
	return \Core\str_to_url($string);
}
