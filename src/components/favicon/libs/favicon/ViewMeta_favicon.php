<?php
/**
 * File for class ViewMeta_favicon definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140323.0350
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
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class ViewMeta_favicon extends ViewMeta {
	public function fetch(){
		$data = [];

		$image = ConfigHandler::Get('/favicon/image');
		if(!$image){
			return [];
		}
		$file = \Core\Filestore\Factory::File($image);
		if(!$file->exists()){
			return [];
		}

		$data['favicon'] = '<link rel="icon" type="image/png" href="' . $file->getPreviewURL('32x32!') . '"/>';
		$data['favicon-apple-touch-icon'] = '<link rel="apple-touch-icon" type="image/png" sizes="72x72" href="' . $file->getPreviewURL('72x72!') . '"/>';
		$data['favicon-apple-touch-icon-2'] = '<link rel="apple-touch-icon" type="image/png" sizes="114x114" href="' . $file->getPreviewURL('114x114!') . '"/>';
		$data['favicon-apple-touch-icon-retina'] = '<link rel="apple-touch-icon" sizes="512x512" type="image/png" href="' . $file->getPreviewURL('512x512!') . '"/>';
		$data['favicon-windows-8'] = '<meta name="msapplication-TileImage" content="' . $file->getPreviewURL('270x270!') . '"/>';

		return $data;
	}
} 