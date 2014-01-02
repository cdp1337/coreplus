<?php
/**
 * Smarty {css} block
 * 
 * @package Core Plus\Core
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
 * @param type $params
 * @param type $template 
 */
function smarty_block_css($params, $innercontent, $template, &$repeat){
	// This only needs to be called once.
	if($repeat) return;
	
	// media type is the first parameter to check for.
	$media = (isset($params['media'])) ? $params['media'] : 'all';

	// See if there's a "href" set.  If so, that's probably an asset.
	// I have a tendency of calling this different things, since things in the head all have
	// different names for this crap!
	// as such, support a bunch of different properties....
	$href = null;
	if(isset($params['href'])) $href = $params['href'];
	elseif(isset($params['link'])) $href = $params['link'];
	elseif(isset($params['src'])) $href = $params['src'];

	// Standard include from an external file.
	if($href !== null){

		// If optional is set, then look up the data to see if it's set.
		if(isset($params['optional']) && $params['optional']){
			$file = $template->template_resource;
			// Trim off the base directory.
			$paths = \Core\Templates\Template::GetPaths();
			foreach($paths as $p){
				if(strpos($file, $p) === 0){
					$file = substr($file, strlen($p));
					break;
				}
			}

			// Look up and see if this css is requested to be loaded by the user.
			$model = TemplateCssModel::Construct($file, $href);
			$enabled = $model->exists() ? $model->get('enabled') : (isset($params['default']) ? $params['default'] : 0);

			if(!$enabled) return;
		}

		\Core\view()->addStylesheet($href, $media);
	}
	// Styles defined inline, fine as well.  The styles will be displayed in the head.
	elseif($innercontent){
		\Core\view()->addStyle($innercontent);
	}
}
