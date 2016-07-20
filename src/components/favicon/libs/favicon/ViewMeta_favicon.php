<?php
/**
 * File for class ViewMeta_favicon definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140323.0350
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
 * A short teaser of what ViewMeta_favicon does.
 *
 * More lengthy description of what ViewMeta_favicon does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for ViewMeta_favicon
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class ViewMeta_favicon extends ViewMeta {
	public function fetch(){
		$data = [];

		$image = ConfigHandler::Get('/favicon/image');
		if($image){
			$closestLargest = \Core\Filestore\Factory::File($image);
		}
		else{
			// Check the theme default.
			$closestLargest = \Core\Filestore\Factory::File('asset/images/favicon.png');
		}
		
		if(!($closestLargest->exists() && $closestLargest->isReadable())){
			// Ensure that the no-page-available icon doesn't show if there is no favicon set!
			$closestLargest = null;
		}
		
		$sizes = [
			'196', // Android Chrome
			'180', // iPhone 6 Plus IOS 8+
			'152', // iPad w/Retina IOS 7
			'120', // iPhone w/Retina IOS 7
			'96', // Google TV
			'76', // iPad IOS 7
			'64', // Nix Desktop
			'60', // iPhone IOS 7
			'48', // Win Desktop
			'32', // Win Task Bar Icon
			'16', // Chrome,IE,FF Tab Icon
		];
		
		foreach($sizes as $s){
			// Get the specific image for this device and this size, (if it exists).
			if(($val = ConfigHandler::Get('/favicon/image-' . $s))){
				$img = \Core\Filestore\Factory::File($val);
				if($img->exists() && $img->isReadable()){
					// This image is good!
					$closestLargest = $img;
				}
			}

			if($closestLargest){
				$sxs = $s . 'x' . $s;
				$url = $closestLargest->getPreviewURL($sxs . '!');
				$data['favicon-' . $s] = '<link rel="shortcut icon" sizes="' . $sxs . '" href="' . $url . '"/>';
				$data['apple-touch-icon-' . $s] = '<link rel="apple-touch-icon" sizes="' . $sxs . '" href="' . $url . '"/>';
			}
		}
		
		return $data;
	}
} 