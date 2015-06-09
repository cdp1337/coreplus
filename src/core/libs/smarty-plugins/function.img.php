<?php
/**
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
 * Render markup for an image tag in smarty
 *
 * The {img} smarty function is the recommended way to load images in templates from asset or public directories.
 * In addition to automatically resolving URLs, it can also handle server-side resizing and a few other nifty features.
 *
 * #### Image Types &amp; Animations
 *
 * As of Core 3.3.0, image types are preserved, so if a .jpg is requested, an image/jpeg is returned.
 * This changed from the previous behaviour where all images were converted to a PNG.
 *
 * Supported image types are `.jp[e]g`, `.png`, and `.gif`.
 *
 * If an animated gif is resized, the server will attempt to preserve the animation on the resized image.
 * This is done via imagemagick, (so that library needs to be present on the server in order for this trick to work).
 *
 * #### SEO Data
 *
 * As of Core 3.2.0, alt tags are automatically added to every image that does not have the alt attribute explicitly set.
 * This alt name is pulled from the filename of the image, with automatic capitalization and '_' => (space) converting.
 *
 * #### Smarty Parameters
 *
 *  * file
 *    * \Core\Filestore\File
 *    * File object passed in to display
 *    * Either "file" or "src" is required.
 *  * src
 *    * string
 *    * Source filename to display.  This can start with "assets" for an asset, or "public" for a public file.
 *    * Either "file" or "src" is required.
 *  * width
 *    * int
 *    * Maximum image width (in pixels).  If both width and height are provided, the image will be constrained to both without any distortion.
 *
 *  * height
 *    * int
 *    * Maximum image height (in pixels).  If both width and height are provided, the image will be constrained to both without any distortion.
 *  * dimensions
 *    * Provide both width and height in pixels, along with special instructions
 *    * Structure is "widthxheight" with no spaces between the "x" and the two integers.
 *    * Special modes available are:
 *    * Carat "`^`" at the end of the string fits the smallest dimension instead of the largest.
 *    * Exclamation mark "`!`" at the end forces size regardless of aspect ratio.
 *    * Greater than "`>`" at the end will only increase image sizes.
 *    * Less than "`<`" at the end will only decrease image sizes.
 *  * placeholder
 *    * string
 *    * placeholder image if the requested image is blank or not found.  Useful for optional fields that should still display something.
 *    * Current values: "building", "generic", "person", "person-tall", "person-wide", "photo"
 *
 * Any other parameter is transparently sent to the resulting `<img/>` tag.
 *
 *
 * #### Example Usage
 *
 * <pre>
 * {img src="public/gallery/photo123.png" width="123" height="123" placeholder="photo" alt="My photo 123"}
 * </pre>
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string
 * @throws SmartyException
 */
function smarty_function_img($params, $smarty){

	// Key/value array of attributes for the resulting HTML.
	$attributes = array();

	if(isset($params['file'])){
		$f = $params['file'];
		if(!$f instanceof \Core\Filestore\File){
			throw new SmartyException('{img} tag expects a \Core\Filestore\File object for the "file" parameter.');
		}
		unset($params['file']);
	}
	elseif(isset($params['src'])){
		$f = \Core\Filestore\Factory::File($params['src']);
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
		$width = preg_replace('#[^0-9]?([0-9]*)x.*#', '$1', $dimensions);
		$height = preg_replace('#.*x([0-9]*)[^0-9]?#', '$1', $dimensions);
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
		$f = \Core\Filestore\Factory::File('assets/images/placeholders/' . $placeholder . '.png');
	}

	if(!$f){
		throw new SmartyException('{img} tag requires either "src", "file", or a "placeholder" parameter.');
	}

	// Do the rest of the attributes that the user sent in (if there are any)
	foreach($params as $k => $v){
		$attributes[$k] = $v;
	}

	if($f instanceof Core\Filestore\Backends\FileRemote){
		// Erm... Give the original URL with the dimension requests.
		$attributes['src'] = $f->getURL();
		if($width) $attributes['width'] = $width;
		if($height) $attributes['height'] = $height;
	}
	else{
		// Try to lookup the preview file.
		// if it exists, then YAY... I can return that direct resource.
		// otherwise, I should check and see if the file is larger than a set filesize.
		// if it is, then I want to return a link to a controller to render that file instead of rendering the file from within the {img} tag.
		//
		// This is useful because any logic contained within this block will halt page execution!
		// To improve the perception of performance, that can be offloaded to the browser requesting the <img/> contents.
		$previewfile = $d ? $f->getQuickPreviewFile($d) : $f;

		if(!$previewfile){
			$attributes['src'] = '#';
			$attributes['title'] = 'No preview files available!';
		}
		elseif(!$previewfile->exists()){
			// Ok, it doesn't exist... return a link to the controller to render this file.
			$attributes['src'] = Core::ResolveLink('/file/preview') . '?f=' . $f->getFilenameHash() . '&d=' . $d;
		}
		else{
			$attributes['src'] = $previewfile->getURL();
		}
	}

	// All images need alt data!
	if(!isset($attributes['alt'])){
		$attributes['alt'] = $f->getTitle();
	}
	
	// Merge them back together in one string.
	$html = '<img';
	foreach($attributes as $k => $v) $html .= " $k=\"$v\"";
	$html .= '/>';

	// If the extended metadata was requested... look that up too!
	if(isset($params['includemeta']) && $params['includemeta']){

		$metahelper  = new \Core\Filestore\FileMetaHelper($f);
		$metacontent = $metahelper->getAsHTML();
		if($metacontent){
			$html = '<div class="image-metadata-wrapper">' . $html . $metacontent . '</div>';
		}
	}

    return $assign ? $smarty->assign($assign, $html) : $html;
}