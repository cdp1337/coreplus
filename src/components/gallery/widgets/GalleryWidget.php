<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 2/1/13
 * Time: 5:16 AM
 * To change this template use File | Settings | File Templates.
 */

use \Core\Forms\Form;

class GalleryWidget extends \Core\Widget {
    
    public $is_simple = true;
    
    /**
     * Array of display settings for this widget.
     * 
     * Widgets loaded into an area support display settings, and each version of the widget
     * loaded into an area has its own unique set of display settings.
     * 
     * @var array
     */
    public $displaySettings = [
		'album' => [
			'type' => 'select',
			'value' => '',
		],
		'count' => [
			'value' => '5',
		],
		'order' => [
			'value' => 'weight',
		],
		'dimensions' => [
			'value' => '100x75',
		],
		'uselightbox' => [
			'value' => false,
		]
	];
	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		// Extend this method in your class and return the path you need.
		// Optional.
		return 'assets/images/previews/templates/widgets/gallery/photo-gallery-140.png';
	}

	public function view(){
		$view = $this->getView();
		
		$order      = $this->getDisplaySetting('order');
		$albumid    = $this->getDisplaySetting('album');
		$count      = $this->getDisplaySetting('count');
		$lightbox   = $this->getDisplaySetting('uselightbox') && Core::IsComponentAvailable('jquery-lightbox');
		$dimensions = $this->getDisplaySetting('dimensions');
		
		$factory = new ModelFactory('GalleryImageModel');
		if($order == 'random'){
			$factory->order('RAND()');
		}
		else{
			$factory->order($order);
		}

		if($albumid){
			$factory->where('albumid = ' . $albumid);
			$album = GalleryAlbumModel::Construct($albumid);
			$link = $album->get('baseurl');
		}
		else{
			$link = null;
		}

		$factory->limit($count);

		$images = $factory->get();

		$view->assign('images', $images);
		$view->assign('dimensions', $dimensions);
		$view->assign('link', $link);
		$view->assign('uselightbox', $lightbox);
	}
}
