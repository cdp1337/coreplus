<?php
/**
 * Gallery listing page, the main interface for all gallery frontend and most backend functions.
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

class GalleryController extends Controller_2_1 {
	/**
	 * Listing page that displays all gallery albums
	 */
	public function index(){
		$view = $this->getView();

		$albums = GalleryAlbumModel::Find(null, null, 'created');
		// Make sure the current user has access to each one.
		foreach($albums as $k => $album){
			/** @var $album GalleryAlbumModels */
			if(!\Core\user()->checkAccess($album->getLink('Page')->get('access'))){
				unset($albums[$k]);
			}
		}

		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');

		// The user is allowed to edit the title now. -- 2013.08
		//$view->title = 'Gallery Listings';

		$view->assignVariable('albums', $albums);

		if($manager){
			// WIP: eh?... haven't decided on this yet.
			//$view->addControl('Gallery Albums Administration', '/gallery/admin', FontAwesome::ICON_DIRECTORY);
			$view->addControl('Gallery Albums Administration', '/gallery/admin', 'directory');
			$view->addControl('Add Album', '/gallery/create', 'add');
			$view->addControl('Edit Gallery Listing Page', '/gallery/updatelisting', 'edit');
		}
	}

	/**
	 * The admin listing version for galleries.
	 *
	 * Will simply display a list of gallery albums on the system.
	 *
	 * @return mixed
	 */
	public function admin() {
		$view = $this->getView();

		if(!$this->setAccess('p:gallery_manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$albums = GalleryAlbumModel::Find(null, null, 'title');

		$view->mastertemplate = 'admin';
		$view->title = 'Gallery Albums Administration';
		$view->assignVariable('albums', $albums);

		$view->addControl('Gallery Albums', '/gallery', 'directory');
		$view->addControl('Add Album', '/gallery/create', 'add');
		$view->addControl('Edit Gallery Listing Page', '/gallery/updatelisting', 'edit');
	}

	/**
	 * Controller method to allow the page metadata to be edited.
	 *
	 * @return int
	 */
	public function updatelisting(){
		$view = $this->getView();
		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$page = new PageModel('/gallery');
		$form = Form::BuildFromModel($page);
		$form->set('callsmethod', 'GalleryController::UpdateListingSave');
		$form->addElement('submit', array('value' => 'Update Listing'));

		$view->title = 'Edit Gallery Listing Page';
		$view->assign('form', $form);
	}

	/**
	 * View a gallery album or an individual image.
	 *
	 * @return int
	 */
	public function view(){
		$req  = $this->getPageRequest();
		$page = $this->getPageModel();

		if(!$this->setAccess($page->get('access'))){
			return View::ERROR_ACCESSDENIED;
		}

		$album = GalleryAlbumModel::Construct($req->getParameter(0));
		$image = GalleryImageModel::Construct($req->getParameter(1));

		if(!$album->exists()) return View::ERROR_NOTFOUND;

		// Since the image view page will have its own page that doesn't inherit the parent's permissions....
		if(!\Core\user()->checkAccess($album->getLink('Page')->get('access'))){
			return View::ERROR_ACCESSDENIED;
		};

		// image view, (there are two parameters)
		if($req->getParameter(1)){
			return $this->_viewImage($image);
		}
		// album view, (only one parameter)
		else{
			return $this->_viewAlbum($album);
		}
	}

	/**
	 * Create a new gallery album
	 *
	 * This is an administrative-only function, ie: p:/gallery/manage_all.
	 *
	 * @return int
	 */
	public function create(){
		$view = $this->getView();

		if(!$this->setAccess('p:/gallery/manage_all')){
			return View::ERROR_ACCESSDENIED;
		}

		$m    = new GalleryAlbumModel();
		$page = $m->getLink('Page');

		$form = new Form();
		$form->addModel($page, 'page');
		$form->addModel($m, 'model');
		$form->set('callsmethod', 'GalleryFormHandler::SaveAlbum');

		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Create'));

		$view->templatename = '/pages/gallery/update.tpl';
		$view->mastertemplate = 'admin';
		$view->title = 'New Gallery Album';
		$view->assignVariable('model', $m);
		$view->assignVariable('form', $form);

		//$view->addControl('All Content Pages', '/Content', 'directory');
	}

	/**
	 * Edit an existing gallery album
	 *
	 * This should be either an administrative, (p:/gallery/manage_all) or editpermission.
	 *
	 * @return int
	 */
	public function edit(){
		$view = $this->getView();
		$req = $this->getPageRequest();

		$album = new GalleryAlbumModel($req->getParameter(0));
		$page  = $album->getLink('Page');

		if(!$album->exists()) return View::ERROR_NOTFOUND;

		$editor  = (\Core\user()->checkAccess($album->get('editpermissions')) || \Core\user()->checkAccess('p:/gallery/manage_all'));
		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');

		if(!($editor || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		$form = new Form();
		$form->addModel($page, 'page');
		$form->addModel($album, 'model');
		$form->set('callsmethod', 'GalleryFormHandler::SaveAlbum');

		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Update'));

		// Editors have certain permissions here, namely limited.
		if($editor && !$manager){
			$form->removeElement('model[nickname]');
			$form->removeElement('model[editpermissions]');
			$form->removeElement('page[rewriteurl]');
			$form->removeElement('page[parenturl]');
		}

		$view->templatename = '/pages/gallery/update.tpl';
		$view->title = 'Edit Gallery Album';
		$view->assignVariable('model', $album);
		$view->assignVariable('form', $form);

		//$view->addControl('All Content Pages', '/Content', 'directory');
	}

	/**
	 * Delete a gallery!
	 *
	 */
	public function delete(){
		$view = $this->getView();
		$req = $this->getPageRequest();

		$album = new GalleryAlbumModel($req->getParameter(0));

		if(!$album->exists()) return View::ERROR_NOTFOUND;

		$editor  = (\Core\user()->checkAccess($album->get('editpermissions')) || \Core\user()->checkAccess('p:/gallery/manage_all'));
		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');

		if(!($editor || $manager)){
			return View::ERROR_ACCESSDENIED;
		}


		$album->delete();
		\core\redirect('/galleryadmin');

	}

	/**
	 * View to rearrange the images in this gallery.
	 *
	 */
	public function order(){
		$view    = $this->getView();
		$request = $this->getPageRequest();
		$albumid = $request->getParameter(0);
		$album   = GalleryAlbumModel::Construct($albumid);
		$images  = $album->getLink('GalleryImage', 'weight');
		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');
		$editor  = (\Core\user()->checkAccess($album->get('editpermissions')) || $manager);
		$uploader  = (\Core\user()->checkAccess($album->get('uploadpermissions')) || $editor);

		// Uploading permission is required at least.
		if(!$uploader) return View::ERROR_ACCESSDENIED;
		// The album must exist first!
		if(!$album->exists()) return View::ERROR_NOTFOUND;

		if($request->isPost()){
			// For sanity reasons, make an array of the current images in this album.
			$imgs = array();
			foreach($images as $i){
				$imgs[] = $i->get('id');
			}
			$weight = 0;

			foreach($_POST['images'] as $i){
				if(!in_array($i, $imgs)) continue;

				$image = GalleryImageModel::Construct($i);
				$image->set('weight', ++$weight);
				$image->save();
			}

			\core\redirect($album->get('rewriteurl'));
		}

		$view->addBreadcrumb($album->get('title'), $album->get('rewriteurl'));
		$view->title = 'Image Order';
		$view->assign('album', $album);
		$view->assign('images', $images);
		$view->assign('editor', $editor);
		$view->assign('manager', $manager);
		$view->assign('uploader', $uploader);
	}


	/**
	 * Handles the upload of new and existing images.  Not meant to be called directly, but is used by the images page.
	 */
	public function images_update(){
		$view      = $this->getView();
		$request   = $this->getPageRequest();
		$albumid   = $request->getParameter(0);
		$album     = new GalleryAlbumModel($albumid);
		$image     = new GalleryImageModel($request->getParameter('image'));
		$manager   = \Core\user()->checkAccess('p:/gallery/manage_all');
		$editor    = (\Core\user()->checkAccess($album->get('editpermissions')) || $manager);
		$uploader  = (\Core\user()->checkAccess($album->get('uploadpermissions')) || $editor);

		if($request->isAjax()){
			// OK
			//$view->mode = View::MODE_AJAX;
			$view->mastertemplate = 'blank.tpl';
		}

		if(!$album->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$uploader){
			return View::ERROR_ACCESSDENIED;
		}

		// Uploaders only can only edit their own image!
		if(!$editor && $image->get('uploaderid') != \Core\user()->get('id')){
			return View::ERROR_ACCESSDENIED;
		}


		if($request->isPost()){
			// This is meant to be loaded in an iframe and rendered from there.
			$view->mode = View::MODE_NOOUTPUT;

			if(!$albumid){
				echo '<div id="error">No album requested</div>';
				return View::ERROR_BADREQUEST;
			}

			if(!$album->exists()){
				echo '<div id="error">Invalid album requested</div>';
				return View::ERROR_NOTFOUND;
			}

			if($image->exists() && $image->get('albumid') != $album->get('id')){
				echo '<div id="error">Invalid album requested</div>';
				return View::ERROR_BADREQUEST;
			}

			// Figure out the largest weight of this album.
			// Note, this is NOT the size of the images, as one or more may have been deleted after uploading.
			$last = GalleryImageModel::Find(array('albumid' => $album->get('id')), 1, 'weight DESC');
			$weight = ($last) ? $last->get('weight') : 0;


			// Multiple images were uploaded, (probably via one of the multi uploaders).
			// In this case, minimal data is available, but enough to save the image.
			if(isset($_POST['images']) && is_array($_POST['images'])){
				$savecount = 0;
				foreach($_POST['images'] as $img){
					// The uploader doesn't prepend the destination directory :/
					$file = $album->getFullUploadDirectory() . $img;

					// instead of just blindly saving this image...
					$image = GalleryImageModel::Find(array('albumid' => $album->get('id'), 'file' => $file));
					if($image) continue; // NO fun....

					// Generate a moderately meaningful title
					$title = substr($img, 0, strrpos($img, '.'));
					$title = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $title);
					$title = trim(preg_replace('/[ ]+/', ' ', $title));
					$title = ucwords($title);


					$image = new GalleryImageModel();
					$image->setFromArray(
						array(
							'albumid' => $album->get('id'),
							'file' => $file,
							'weight' => ++$weight,
							'title' => $title,
						)
					);
					$image->save();
					$savecount++;

					// And the metadata for this element
					$metas = new \Core\Filestore\FileMetaHelper($image->getOriginalFile());
					$metas->setMeta('title', $title);

				}
				if($savecount == 0){
					$action = 'No files uploaded';
					$actiontype = 'info';
				}
				else{
					$action = 'Added ' . $savecount . ' Files!';
					$actiontype = 'success';
				}
				$imageid = '';
			}
			else{
				// This POST is only for multiple files!
				return view::ERROR_BADREQUEST;
			}

			if($request->isAjax()){
				// This will be rendered with jquery, so it'll be data-esque.
				echo '<div id="success">' . $action . '</div>' . '<div id="imageid">' . $imageid . '</div>';
				Core::SetMessage($action, $actiontype);
				return;
			}
			else{
				Core::SetMessage($action, $actiontype);
				\core\redirect((isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $album->get('rewriteurl')));
			}
		}
		else{



			if(!$albumid){
				return View::ERROR_BADREQUEST;
			}

			if(!$album->exists()){
				return View::ERROR_NOTFOUND;
			}

			if($image->exists() && $image->get('albumid') != $album->get('id')){
				return View::ERROR_BADREQUEST;
			}

			$file = $image->getOriginalFile();
			$meta = new \Core\Filestore\FileMetaHelper($file);

			$form = new Form();
			$form->addModel($image, 'image');
			$meta->addElementsToForm($form, 'metas');

			// If this file exists and it's not an image.... enable the preview uploader.
			if($image->exists() && !$file->isImage()){
				$form->switchElementType('image[previewfile]', 'file');
			}

			$form->set('callsmethod', 'GalleryController::ImagesUpdateSaveHandler');
			$form->addElement('submit', ['name' => 'submit', 'value' => 'Save']);

			$view->title = 'Editing image ' . $image->get('title');
			$view->assign('image', $image);
			$view->assign('album', $album);
			$view->assign('form', $form);
			$view->assign('savetext', ($image->exists() ? 'Update' : 'Upload'));

		}
	}

	public function images_delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$albumid = $request->getParameter(0);
		$album   = new GalleryAlbumModel($albumid);
		$image   = new GalleryImageModel($request->getParameter('image'));

		if(!$albumid){
			return View::ERROR_BADREQUEST;
		}

		if(!$album->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$image->exists()){
			return View::ERROR_NOTFOUND;
		}

		if($image->get('albumid') != $album->get('id')){
			return View::ERROR_BADREQUEST;
		}

		$image->delete();

		Core::SetMessage('Removed image successfully', 'success');
		\core\redirect($album->get('rewriteurl'));
	}

	public function images_rotate(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!$request->isJSON()){
			return View::ERROR_BADREQUEST;
		}

		$albumid = $request->getParameter(0);
		$album   = new GalleryAlbumModel($albumid);
		$image   = new GalleryImageModel($request->getParameter('image'));
		$rotate  = $request->getParameter('rotate');
		$view->contenttype = View::CTYPE_JSON;

		if(!$albumid){
			return View::ERROR_BADREQUEST;
		}

		if(!$album->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$image->exists()){
			return View::ERROR_NOTFOUND;
		}

		if($image->get('albumid') != $album->get('id')){
			return View::ERROR_BADREQUEST;
		}

		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');
		$editor  = (\Core\user()->checkAccess($album->get('editpermissions')) || $manager);
		$uploader  = (\Core\user()->checkAccess($album->get('uploadpermissions')) || $editor);

		if(!$uploader){
			return View::ERROR_ACCESSDENIED;
		}

		// Uploaders only can only edit their own image!
		if(!$editor && $image->get('uploaderid') != \Core\user()->get('id')){
			return View::ERROR_ACCESSDENIED;
		}

		// According to GD:
		//       0
		//  90       270
		//      180

		// The new rotation is based on the previous and the direction.
		$current = $image->get('rotation');
		$new = null;
		if($rotate == 'cw'){
			if($current == 0)      $new = 270;
			elseif($current > 270) $new = 270;
			elseif($current > 180) $new = 180;
			elseif($current > 90)  $new = 90;
			else                   $new = 0;
		}
		else{
			if($current == 0)      $new = 90;
			elseif($current < 90)  $new = 90;
			elseif($current < 180) $new = 180;
			elseif($current < 270) $new = 270;
			else                   $new = 0;
		}

		$image->set('rotation', $new);
		$image->save();

		$view->jsondata = array('status' => '1', 'message' => 'Rotated image');
	}

	private function _viewImage(GalleryImageModel $image){

		$album = $image->getLink('GalleryAlbum');
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');
		$editor  = (\Core\user()->checkAccess($album->get('editpermissions')) || $manager);
		$uploader = (\Core\user()->checkAccess($album->get('uploadpermissions')) || $editor);

		// Uploaders only can only edit their own image!
		if(!$editor && $image->get('uploaderid') != \Core\user()->get('id')){
			$uploader = false;
		}

		// I still need all the other images to know where this image lies in the stack!
		$images = $album->getLink('GalleryImage', 'weight');

		if(!$image->exists()){
			return View::ERROR_NOTFOUND;
		}

		if($image->get('albumid') != $album->get('id')){
			return View::ERROR_NOTFOUND;
		}

		$id    = $image->get('id');
		$link  = $image->getRewriteURL();
		$exif  = $image->getExif();
		$metas = new \Core\Filestore\FileMetaHelper($image->getOriginalFile());

		//var_dump($exif); die();
		/*

		foreach($exif as $k => $v){
			echo $k . ': {$exif.' . $k . '}&lt;br/>' . '//' . $v . "<br/>\n";
		}
		var_dump($exif); die();
		*/
		$next = null;
		$prev = null;
		$lastnum = sizeof($images) - 1;
		// Determine the next/prev array.
		foreach($images as $k => $img){
			if($img->get('id') == $id){
				// Found it! is it first or last?
				if($k == 0 && $k == $lastnum){
					// both are blank.... well how 'bout that :/
				}
				elseif($k == 0){
					// It's the first image.
					$next = $images[$k + 1];
				}
				elseif($k == $lastnum){
					// It's the last image.
					$prev = $images[$k - 1];
				}
				else{
					// It's somewhere in between :)
					$next = $images[$k + 1];
					$prev = $images[$k - 1];
				}
			}
		}

		$view->mode = View::MODE_PAGEORAJAX;
		$view->templatename = '/pages/gallery/view-' . $image->getFileType() . '.tpl';
		$view->assign('image', $image);
		$view->assign('album', $album);
		$view->assign('lightbox_available', Core::IsComponentAvailable('jquery-lightbox'));
		$view->assign('editor', $editor);
		$view->assign('manager', $manager);
		$view->assign('uploader', $uploader);
		$view->assign('prev', $prev);
		$view->assign('next', $next);
		$view->assign('exif', $exif);
		$view->assign('metas', $metas);
		$view->updated = $image->get('updated');
		$view->canonicalurl = Core::ResolveLink($link);
		if(is_array($metas->getMetaTitle('keywords'))){
			$view->meta['keywords'] = implode(', ', $metas->getMetaTitle('keywords'));
		}
		$view->meta['description'] = $metas->getMetaTitle('description');
		$view->meta['og:image'] = $image->getFile()->getPreviewURL('200x200');
		//$view->addBreadcrumb($album->get('title'), $album->get('baseurl'));
		$view->title = ($image->get('title') ? $image->get('title') : 'Image Details');
		// This is needed to prevent the parent from overriding the seotitle.
		$view->meta['title'] = $view->title;
		if($editor){
			$view->addControl(
				array(
					'title' => 'Edit Image',
					'link' => 'gallery/images/update/' . $album->get('id') . '?image=' . $image->get('id'),
					'class' => 'ajax-link',
					'icon' => 'edit',
					'image' => $image->get('id'),
				)
			);
			$view->addControl(
				array(
					'title' => 'Rotate CCW',
					'link' => '#',
					'class' => 'rotate-link',
					'icon' => 'undo',
					'image' => $image->get('id'),
					'rotate' => 'ccw',
				)
			);
			$view->addControl(
				array(
					'title' => 'Rotate CW',
					'link' => '#',
					'class' => 'rotate-link',
					'icon' => 'repeat',
					'image' => $image->get('id'),
					'rotate' => 'cw',
				)
			);
			$view->addControl(
				array(
					'title' => 'Remove Image',
					'link' => 'gallery/images/delete/' . $album->get('id') . '?image=' . $image->get('id'),
					'confirm' => 'Confirm deleting image?',
					'icon' => 'remove',
				)
			);
		}
	}

	private function _viewAlbum(GalleryAlbumModel $album){

		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/gallery/manage_all');
		$editor  = (\Core\user()->checkAccess($album->get('editpermissions')) || $manager);
		$uploader = (\Core\user()->checkAccess($album->get('uploadpermissions')) || $editor);

		$url = $album->get('rewriteurl');
		$images = $album->getLink('GalleryImage', 'weight');
		$lastupdated = $album->get('updated');


		if($uploader){
			$uploadform = new Form();
			$uploadform->set('action', Core::ResolveLink('/gallery/images_update/' . $album->get('id')));
			$uploadform->addElement(
				'multifile',
				array(
					'basedir' => $album->getFullUploadDirectory(),
					'title' => 'Bulk Upload Files',
					'name' => 'images',
					'accept' => $album->get('accepttypes'),
				)
			);
			$uploadform->addElement('submit', array('value' => 'Save Gallery Changes'));
		}
		else{
			$uploadform = false;
		}

		// I need to attach a friendly URL for each image.
		// This gets a little tricky since each image doesn't have a unique title necessarily.
		foreach($images as $i){
			// I would like to know when the last change overall was, not just for the gallery.
			$lastupdated = max($lastupdated, $i->get('updated'));
		}

		$view->templatename = '/pages/gallery/view.tpl';
		$view->assign('album', $album);
		$view->assign('images', $images);
		$view->assign('editor', $editor);
		$view->assign('manager', $manager);
		$view->assign('uploader', $uploader);
		$view->assign('uploadform', $uploadform);
		$view->assign('userid', \Core\user()->get('id'));
		$view->updated = $lastupdated;

		// If there are images in this gallery, grab the first one to show as a preview!
		if(count($images)){
			$view->meta['og:image'] = $images[0]->getFile()->getPreviewURL('200x200');
		}

		if($editor){
			$view->addControl('Gallery Albums Administration', '/gallery/admin', 'directory');
			$view->addControl('Edit Gallery Album', '/gallery/edit/' . $album->get('id'), 'edit');
		}
		if($uploader){
			// If they can upload images, they can rearrange them!
			$view->addControl(
				array(
					'title' => 'Rearrange Images',
					'link' => '/gallery/order/' . $album->get('id'),
					'icon' => 'move',
				)
			);
		}
	}

	public static function ImagesUpdateSaveHandler(Form $form) {
		try{
			/** @var $image GalleryImageModel */
			$image = $form->getModel('image');
		}
		catch(Exception $e){
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}

		$metas = new \Core\Filestore\FileMetaHelper($image->getOriginalFile());

		// Set the meta information from the form.
		foreach($form->getElements() as $el){
			/** @var $el FormElement */
			$name = $el->get('name');
			if(strpos($name, 'metas[') !== 0) continue;

			$name = substr($name, 6, -1);
			$metas->setMeta($name, $el->get('value'));
		}

		// If no title was set, I need to pick one by default.
		if(!$metas->getMetaTitle('title')){
			// Generate a moderately meaningful title from the filename.
			$title = $image->getOriginalFile()->getBasename(true);
			$title = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $title);
			$title = trim(preg_replace('/[ ]+/', ' ', $title));
			$title = ucwords($title);
			$metas->setMeta('title', $title);
		}


		if(!$image->exists()){
			// Figure out the largest weight of this album.
			// Note, this is NOT the size of the images, as one or more may have been deleted after uploading.
			$last = GalleryImageModel::Find(array('albumid' => $image->get('albumid')), 1, 'weight DESC');
			$weight = ($last) ? $last->get('weight') : 0;

			$image->set('weight', $weight);
		}

		// Don't forget to keep the metatags in sync!
		$image->set('title', $metas->getMetaTitle('title'));

		$action = $image->exists() ? 'Updated' : 'Added';

		$image->save();
		Core::SetMessage($action . ' image successfully!', 'success');
		return true;
	}

	/**
	 * Save just the gallery listing page itself.  This doesn't actually have any administrable data
	 * associated to it, other than the page.
	 *
	 * @static
	 * @param Form $form
	 */
	public static function UpdateListingSave(Form $form) {
		$model = $form->getModel();
		$model->save();

		// w00t
		return $model->getResolvedURL();
	}
}
