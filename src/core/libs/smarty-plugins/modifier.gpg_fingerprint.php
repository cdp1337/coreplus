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
 * Type:     modifier<br>
 * Name:     gpg_fingerprint<br>
 *
 * @param string $string input string
 *
 * @return string HTML text
 */
function smarty_modifier_gpg_fingerprint($string) {
	return \Core\GPG\GPG::FormatFingerprint($string, true, true);
}
