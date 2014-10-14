<?php
/**
 * File for the smarty "a" block function
 *
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
 * Return a valid resolved "A" tag, with whatever inner content preserved.
 *
 * This is the recommended way to handle &lt;a/&gt; tags in Core templates.
 * The href attribute is automatically resolved to the primary rewrite URL,
 * and all additional links, (mostly), are passed through to the template as-is.
 *
 * #### Smarty Parameters
 *
 *  * assign
 *    * Assign the result to a variable instead of printing it to stdout.
 *  * confirm
 *    * Set to a string to prompt the user with the string before submitting the link via a POST request.
 *  * history
 *    * Set to a number, (1, 2, etc), to set the href to that user's last nth page from history.
 *
 * #### Standard Example
 *
 * <pre>
 * // The smarty line
 * {a href="/content/view/1"}Something Blah{/a}
 * // Resolves to
 * &lt;a href="/homepage"&gt;Something Blah&lt;/a&gt;
 * </pre>
 *
 * #### External Link
 *
 * Also works with already-resolved or external links
 *
 * <pre>
 * {a href="http://corepl.us" target="_blank"}Link Somewhere!{/a}
 * // Resolves to
 * &lt;a href="http://corepl.use" target="_blank"&gt;Link Somewhere!&lt;/a&gt;
 * </pre>
 *
 * #### Multisite Links
 *
 * When multi-site is installed and activated, cross-site links can also be used that resolve based on that site's criteria.
 *
 * <pre>
 * {a href="site:12/content/view/5"}Child #12 About Page{/a}
 * // Resolves to
 * &lt;a href="http://child-12-url.example.com/about-us"&gt;Child #12 About Page&lt;/a&gt;
 * </pre>
 *
 * @param array       $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param string|null $content Null on opening pass, rendered source of the contents inside the block on closing pass
 * @param Smarty      $smarty  Parent Smarty template object
 * @param boolean     $repeat  True at the first call of the block-function (the opening tag) and
 * false on all subsequent calls to the block function (the block's closing tag).
 * Each time the function implementation returns with $repeat being TRUE,
 * the contents between {func}...{/func} are evaluated and the function implementation
 * is called again with the new block contents in the parameter $content.
 *
 * @return string
 */
function smarty_block_a($params, $content, $smarty, &$repeat){
	// This only needs to be called once.
	if($repeat) return '';

	$assign= false;

	// Start the A tag
	$newcontent = '<a';

	// Allow "confirm" text to override the href and onClick functions.
	// This has the cool ability of not requiring jquery to run, since it is all handled with PHP logic.
	if(isset($params['confirm'])){
		$params['onclick'] = 'return Core.ConfirmEvent(this);';
		// @todo Convert these to the data- prefix instead of data:
		$params['data:href'] = Core::ResolveLink($params['href']);
		$params['data:confirm'] = $params['confirm'];
		//$params['onClick'] = "if(confirm('" . str_replace("'", "\\'", $params['confirm']) . "')){ Core.PostURL('" . str_replace("'", "\\'", Core::ResolveLink($params['href'])) . "'); } return false;";
		$params['href'] = '#false';
	}

	// Add in any attributes.
	foreach($params as $k => $v){
		$k = strtolower($k);
		switch($k){
			case 'href':
				$newcontent .= ' href="' . Core::ResolveLink ($v) . '"';
				break;
			case 'history':
				$newcontent .= ' href="' . Core::GetHistory($v) . '"';
				break;
			case 'assign':
				$assign = $v;
				break;
			default:
				$newcontent .= " $k=\"" . str_replace('"', '&quot;', $v) . "\"";
		}
	}
	// Close the starting tag.
	$newcontent .= '>';

	// Add any content inside.
	$newcontent .= $content;

	// Close the set.
	$newcontent .= '</a>';

	return $assign ? $smarty->assign($assign, $newcontent) : $newcontent;
}