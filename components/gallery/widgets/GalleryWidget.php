<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 2/1/13
 * Time: 5:16 AM
 * To change this template use File | Settings | File Templates.
 */
class GalleryWidget extends Widget_2_1 {
	public function view(){
		$view = $this->getView();

		$factory = new ModelFactory('GalleryImageModel');
		if($this->getSetting('order') == 'random'){
			$factory->order('RAND()');
		}
		else{
			$factory->order($this->getSetting('order'));
		}

		if($this->getSetting('album')){
			$factory->where('albumid = ' . $this->getSetting('album'));
			$album = GalleryAlbumModel::Construct($this->getSetting('album'));
			$link = $album->get('baseurl');
		}
		else{
			$link = null;
		}

		$factory->limit($this->getSetting('count'));

		$images = $factory->get();

		$view->assign('images', $images);
		$view->assign('dimensions', $this->getSetting('dimensions'));
		$view->assign('link', $link);
		$view->assign('uselightbox', ($this->getSetting('uselightbox') && Core::IsComponentAvailable('jquery-lightbox')));
	}
}
