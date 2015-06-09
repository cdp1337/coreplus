<?php
/**
 * File for the "access" smarty block function
 *
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * A block to only render the inside content if the access string passes.
 *
 * #### Smarty Parameters
 *
 * The only parameter required is the one access string, (wrapped in quotes), to check for.
 * This parameter does not require any key prefix.
 *
 * #### Example Usage
 *
 * Have a paragraph only visible to super admins.
 *
 * <pre>
 * {access "g:admin"}
 * &lt;p&gt;This snippet is only visible to administrators.&lt;/p&gt;
 * {/access}
 * </pre>
 *
 * Have a paragraph only visible to the user groups "group1" or "group2".
 *
 * <pre>
 * {access 'g:group1;g:group2'}
 * &lt;p&gt;While this snippet is visible to everyone except admins.&lt;/p&gt;
 * {/access}
 * </pre>
 *
 * Have a paragraph only visible to users who are in user groups that have the "/something/blah" permission.
 *
 * <pre>
 * {access 'p:/something/blah'}
 * &lt;p&gt;Content only for people with the /something/blah permission.&lt;/p&gt;
 * {/access}
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
function smarty_block_access($params, $content, $smarty, &$repeat){
	// This only needs to be called once.
	if($repeat) return '';

	$str = $params[0];

	$result = \Core\user()->checkAccess($str);

	return $result ? $content : null;
}