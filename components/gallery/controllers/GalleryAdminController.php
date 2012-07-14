<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/9/12
 * Time: 4:49 PM
 * To change this template use File | Settings | File Templates.
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
