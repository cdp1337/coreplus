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

function smarty_block_insertable($params, $content, $template, &$repeat){

	$assign = (isset($params['assign']))? $params['assign'] : false;

	// This only needs to be called once.
	// If a value is being assigned, then it's on the first pass so the value will be assigned by the time the content is hit.
	if($assign){
		if($repeat){
			// Running the first time with an assign variable, OK!
		}
		else{
			return $content;
		}
	}
	else{
		// No assign requested, run on the second only.
		if($repeat){
			return '';
		}
		else{
			// Continue!
		}
	}

	// I need to use the parent to lookup the current base url.
	$baseurl = PageRequest::GetSystemRequest()->getBaseURL();

	if(!isset($params['name'])) return '';


	$i = InsertableModel::Construct($baseurl, $params['name']);

	if($i->exists()){
		$value = $i->get('value');
	}

	// Replace images with the optimized version.
	if(strpos($value, '<img') !== false){
		$x = 0;
		$imagestart = null;

		while($x < strlen($value)){
			if(substr($value, $x, 4) == '<img'){
				$imagestart = $x;
				$x+= 3;
				continue;
			}

			if($imagestart !== null && ($value{$x} == '>' || substr($value, $x, 2) == '/>')){
				// This will equal the full image HTML tag, ie: <img src="blah"/>...
				$fullimagetag = substr($value, $imagestart, $x-1);

				// Convert it to a DOM element so I can process it.
				$simple = new SimpleXMLElement($fullimagetag);
				$attributes = array();
				foreach($simple->attributes() as $k => $v){
					$attributes[$k] = (string)$v;
				}

				if(isset($attributes['width']) || isset($attributes['height'])){
					$file = \Core\Filestore\Factory::File($attributes['src']);

					if(isset($attributes['width']) && isset($attributes['height'])){
						$dimension = $attributes['width'] . 'x' . $attributes['height'] . '!';
						unset($attributes['width'], $attributes['height']);
					}
					elseif(isset($attributes['width'])){
						$dimension = $attributes['width'];
						unset($attributes['width']);
					}
					else{
						$dimension = $attributes['height'];
						unset($attributes['height']);
					}

					$attributes['src'] = $file->getPreviewURL($dimension);

					// And rebuild.
					$img = '<img';
					foreach($attributes as $k => $v){
						$img .= ' ' . $k . '="' . str_replace('"', '&quot;', $v) . '"';
					}
					$img .= '/>';

					// Figure out the offset for X.  I'll need to modify this after I merge it in.
					$x += strlen($img) - strlen($fullimagetag);
					// Split this string back in.
					$value = substr_replace($value, $img, $imagestart, strlen($fullimagetag));
				}
				// Reset...
				$imagestart = null;
			}
			$x++;
		}
	}
	//var_dump($value);

	if($assign){
		$template->assign($assign, $value);
	}
	else{
		return $value;
	}
}