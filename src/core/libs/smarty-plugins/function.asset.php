<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
 * Resolve an asset to a fully-resolved URL from within Smarty.
 *
 * This is the recommended way to handle asset url resolving from within templates.
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string
 */
function smarty_function_asset($params, $smarty){

	// I don't really care what it's called!
	if(isset($params['file']))     $file = $params['file'];
	elseif(isset($params['src']))  $file = $params['src'];
	elseif(isset($params['href'])) $file = $params['href'];
	elseif(isset($params[0]))      $file = $params[0];
	else                           $file = 'images/404.jpg';

	// Allow already-resolved links to be returned verbatim.
	if(strpos($file, '://') !== false){
		// @TODO
		return $file;
	}

	// Since an asset is just a file, I'll use the builtin file store system.
	// (although every file coming in should be assumed to be an asset, so
	//  allow for a partial path name to come in, assuming asset/).
	if(strpos($file, 'asset/') === 0){
		$file = 'assets/' . substr($file, 7);
	}
	if(strpos($file, 'assets/') !== 0){
		$file = 'assets/' . $file;
	}

	$f = \Core\Filestore\Factory::File($file);

	$dimensions = $width = $height = null;
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
		$width = preg_replace('#[^0-9]?([0-9]*)x.*#', '$1', $dimensions);
		$height = preg_replace('#.*x([0-9]*)[^0-9]?#', '$1', $dimensions);
		unset($params['dimensions']);
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
	
	// New support for dynamic resizing from within the {asset} tag!
	if($d){
		$url = $f->getPreviewURL($d);
	}
	else{
		$url = $f->getURL();
	}

	if(isset($params['assign'])){
		$smarty->assign($params['assign'], $url);
	}
	else{
		return $url;
	}
}
