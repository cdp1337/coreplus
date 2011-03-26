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
class NavigationModel extends Model{
    public function __construct($key = null) {
		$this->_linked = array(
			'Widget' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
			'NavigationEntry' => array(
				'link' => Model::LINK_HASMANY,
				'on' => array('navigationid' => 'id'),
			),
		);
		
		parent::__construct($key);
	}
	
	public function get($k) {
		$k = strtolower($k);
		switch($k){
			case 'baseurl':
				return '/Navigation/View/' . $this->_data['id'];
				break;
			default:
				return parent::get($k);
		}
	}
}
?>
