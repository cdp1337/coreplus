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
			'title' => 't:STRING_GALLERY_WIDGET_ALBUM_SELECT',
			'description' => 't:MESSAGE_GALLERY_WIDGET_ALBUM_SELECT',
			'value' => '',
			'source' => 'GalleryWidget::GetAlbumsAsOptions',
		],
		'count' => [
			'type' => 'select',
			'title' => 't:STRING_GALLERY_WIDGET_COUNT',
			'description' => 't:MESSAGE_GALLERY_WIDGET_COUNT',
			'value' => '5',
			'options' => [
				1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 
				11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
				21, 22, 23, 24, 25, 26, 27, 28, 29, 30
			]
		],
		'order' => [
			'type' => 'select',
			'title' => 't:STRING_GALLERY_WIDGET_ORDER',
			'description' => 't:MESSAGE_GALLERY_WIDGET_ORDER',
			'value' => 'weight',
			'options' => [
				'weight' => 'Standard Order',
				'created desc' => 'Date (Newest First)',
				'random' => 'Random',
			]
		],
		'dimensions' => [
			'title' => 't:STRING_GALLERY_WIDGET_DIMENSIONS',
			'description' => 't:MESSAGE_GALLERY_WIDGET_DIMENSIONS',
			'value' => '100x75',
		],
		'useajax' => [
			'type' => 'checkbox',
			'title' => 't:STRING_GALLERY_WIDGET_USE_AJAX',
			'description' => 't:MESSAGE_GALLERY_WIDGET_USE_AJAX',
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
		$lightbox   = $this->getDisplaySetting('useajax') && Core::IsComponentAvailable('jquery-lightbox');
		$dimensions = $this->getDisplaySetting('dimensions');
		
		// @todo Lightbox viewing is broke currently, fix this!
		$lightbox = false;
		
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
	
	/**
	 * Get the gallery albums on this site as options; useful for the album option.
	 * 
	 * @return array
	 */
	public static function GetAlbumsAsOptions(){
		$albums = GalleryAlbumModel::Find(null, null, 'title');
		$albumopts = array('' => 'All Galleries');
		foreach($albums as $album){
			$albumopts[ $album->get('id') ] = $album->get('title');
		}
		
		return $albumopts;
	}
}
