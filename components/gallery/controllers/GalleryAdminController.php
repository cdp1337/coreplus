<?php
/**
 * Gallery admin listing page, mainly acts as a placeholder for the admin menu.
 *
 * @package Gallery
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2012  Charlie Powell
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

class GalleryAdminController extends Controller_2_1{
	public function __construct(){
		// Generic admin permission for this system.
		// @todo Expand this to include more fine-grain control over permissions of individual galleries.
		$this->accessstring = 'g:admin';
	}

	public function index(){
		$view = $this->getView();

		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$albums = GalleryAlbumModel::Find(null, null, null);


		$view->templatename = '/pages/galleryadmin/index.tpl';
		$view->title = 'Gallery Administration';
		$view->assignVariable('albums', $albums);
		$view->addControl('Add Album', '/gallery/create', 'add');
	}
}
