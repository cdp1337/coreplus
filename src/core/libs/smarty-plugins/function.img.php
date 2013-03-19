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
 * @param $params
 * @param $template
 *
 * @return string
 * @throws SmartyException
 */
function smarty_function_img($params, $template){
	
	// Key/value array of attributes for the resulting HTML.
	$attributes = array();

	if(isset($params['file'])){
		$f = $params['file'];
		if(!$f instanceof File_Backend){
			throw new SmartyException('{img} tag expects a File object for the "file" parameter.');
		}
		unset($params['file']);
	}
	elseif(isset($params['src'])){
		$f = Core::File($params['src']);
		unset($params['src']);
	}
	else{
		$f = null;
	}
	
	// Some optional parameters, (and their defaults)
	$assign = $width = $height = $dimensions = false;
	$placeholder = null;
	
	if(isset($params['assign'])){
		$assign = $params['assign'];
		unset($params['assign']);
	}
	
	if(isset($params['width'])){
		$width = $params['width'];
		unset($params['width']);
	}
	
	if(isset($params['height'])){
		$height = $params['height'];
		unset($params['height']);
	}

	if(isset($params['dimensions'])){
		$dimensions = $params['dimensions'];
		unset($params['dimensions']);
	}

	if(isset($params['placeholder'])){
		$placeholder = $params['placeholder'];
		unset($params['placeholder']);
	}
	

	if($dimensions){
		// Passing in dimensions raw will allow the user more control over the size of the images.
		$d = $dimensions;
	}
	else{
		// If one is provided but not the other, just make them the same.
		if($width && !$height) $height = $width;
		if($height && !$width) $width = $height;

		$d = ($width && $height) ? $width . 'x' . $height : false;
	}



	// If the file doesn't exist and a placeholder was provided, use the appropriate placeholder image!
	if(!($f && $f->exists() && $f->isImage()) && $placeholder){
		// Try that!
		$f = new File_local_backend('assets/images/placeholders/' . $placeholder . '.png');
	}

	if(!$f){
		throw new SmartyException('{img} tag requires either "src", "file", or a "placeholder" parameter.');
	}

	// Do the rest of the attributes that the user sent in (if there are any)
	foreach($params as $k => $v){
		$attributes[$k] = $v;
	}

	// Well...
	if($d){
		$attributes['src'] = $f->getPreviewURL($d);
	}
	else{
		$attributes['src'] = $f->getURL();
	}
	
	// Merge them back together in one string.
	$html = '<img';
	foreach($attributes as $k => $v) $html .= " $k=\"$v\"";
	$html .= '/>';
	
    return $assign ? $template->assign($assign, $html) : $html;
}