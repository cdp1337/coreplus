<?php
/**
 * Smarty plugin
 *
 * @package Core
 * @subpackage PluginsModifier
 */

/**
 * Convert a raw user agent string to something more readable for most humans.
 *
 * Type:     modifier<br>
 * Name:     user_agent<br>
 *
 * @param string $string input string
 *
 * @return string HTML text
 */
function smarty_modifier_user_agent($string) {
	$ua = new \Core\UserAgent($string);
	return $ua->getAsHTML();
}
