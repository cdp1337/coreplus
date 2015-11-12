<?php
/**
 * Smarty plugin
 *
 * @package Core
 * @subpackage PluginsModifier
 */

/**
 * Convert markdown text to HTML.
 *
 * Type:     modifier<br>
 * Name:     md_to_html<br>
 *
 * @param string $string input string
 *
 * @return string HTML text
 */
function smarty_modifier_md_to_html($string) {
	return \Core\MarkdownProcessor::DefaultTransform($string);
}
