<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContentModel
 *
 * @author powellc
 */
class ContentModel extends Model{
	
	protected $_schema = array(
		'id' => array(),
		'title' => array(),
		'description' => array(),
		'keywords' => array(),
		'access' => array(),
	);
	
    public function __construct($key = null) {
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
		);
		
		parent::__construct($key);
	}
	
	public function get($k) {
		$k = strtolower($k);
		switch($k){
			case 'baseurl':
				return '/Content/View/' . $this->_data['id'];
				break;
			default:
				return parent::get($k);
		}
	}
}
?>
