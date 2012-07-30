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
		throw new SmartyException('{img} tag requires either a "src" or "file" parameter.');
	}
	
	// Some optional parameters, (and their defaults)
	$assign = $width = $height = false;
	
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
	
	
	// If one is provided but not the other, just make them the same.
	if($width && !$height) $height = $width;
	if($height && !$width) $width = $height;
	
	$d = ($width && $height) ? $width . 'x' . $height : false;
	
	// Well...
	if($d){
		$attributes['src'] = $f->getPreviewURL($d);
	}
	else{
		$attributes['src'] = $f->getURL();
	}
	
	// Do the rest of the attributes that the user sent in (if there are any)
	foreach($params as $k => $v){
		$attributes[$k] = $v;
	}
	
	// Merge them back together in one string.
	$html = '<img';
	foreach($attributes as $k => $v) $html .= " $k=\"$v\"";
	$html .= '/>';
	
    return $assign ? $template->assign($assign, $html) : $html;
}