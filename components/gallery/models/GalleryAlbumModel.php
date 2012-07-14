<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/9/12
 * Time: 4:51 PM
 * To change this template use File | Settings | File Templates.
 */
class GalleryAlbumModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'paginate' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
			'default' => 15,
			'formtitle' => 'Per-Page Count',
			'formdescription' => 'The number of results per page, set to 0 to disable pagination.'
		),
		'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'required' => true,
			'default' => true,
			'formdescription' => 'Disable this album to hide from the public listings.'
		),
		'store_type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('public', 'private'),
			'default' => 'public',
			'formdescription' => 'Change the store location for this gallery.
				Public means that images are accessible directly and can be served very quickly.
				Private means that images are only available through the framework and thus more secure, but are slower to be served.',
			'formtype' => 'hidden', // Set this to hidden right now, support for private galleries will be added in the future.
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
	);

	public function __construct($key = null) {
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
			'GalleryImage' => array(
				'link' => Model::LINK_HASMANY,
				'on' => array('albumid' => 'id'),
			),
		);

		parent::__construct($key);
	}

	public function get($k) {
		$k = strtolower($k);
		switch($k){
			case 'baseurl':
				return '/gallery/view/' . $this->_data['id'];
				break;
			case 'title':
				return $this->getLink('Page')->get('title');
				break;
			case 'rewriteurl':
				return $this->getLink('Page')->get('rewriteurl');
				break;
			default:
				return parent::get($k);
		}
	}
}
