<?php
/**
 * @package Core Plus\Core
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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
 * Return a valid resolved a tag, with whatever inner content preserved.
 *
 * @param $params array
 * @param $innercontent string
 * @param $template Smarty
 * @param $repeat boolean
 *
 * @return string
 */
function smarty_block_a($params, $innercontent, $template, &$repeat){
	// This only needs to be called once.
	if($repeat) return '';

	$assign= false;

	// Start the A tag
	$content = '<a';

	// Allow "confirm" text to override the href and onClick functions.
	// This has the cool ability of not requiring jquery to run, since it is all handled with PHP logic.
	if(isset($params['confirm']) && $params['confirm']){
		$params['onclick'] = 'return Core.ConfirmEvent(this);';
		$params['data:href'] = Core::ResolveLink($params['href']);
		$params['data:confirm'] = $params['confirm'];
		//$params['onClick'] = "if(confirm('" . str_replace("'", "\\'", $params['confirm']) . "')){ Core.PostURL('" . str_replace("'", "\\'", Core::ResolveLink($params['href'])) . "'); } return false;";
		$params['href'] = '#false';
	}

	if(isset($params['history'])) $href = Core::GetHistory($params['history']);

	// Add in any attributes.
	foreach($params as $k => $v){
		$k = strtolower($k);
		switch($k){
			case 'href':
				$content .= ' href="' . Core::ResolveLink ($v) . '"';
				break;
			case 'history':
				$content .= ' href="' . Core::GetHistory($v) . '"';
				break;
			case 'assign':
				$assign = $v;
				break;
			default:
				$content .= " $k=\"" . str_replace('"', '&quot;', $v) . "\"";
		}
	}
	// Close the starting tag.
	$content .= '>';

	// Add any content inside.
	$content .= $innercontent;

	// Close the set.
	$content .= '</a>';

	return $assign ? $template->assign($assign, $content) : $content;
}