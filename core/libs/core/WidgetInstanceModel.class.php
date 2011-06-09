<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PageModel
 *
 * @author powellc
 */
class WidgetInstanceModel extends Model{

	
	public function  __construct($key = null) {

		// Set some defaults first.
		// @todo Make these automatic somehow.
		$this->_updatedcolumn = 'updated';
		$this->_createdcolumn = 'created';
		
		/*$this->_linked = array(
			'Widget' => array(
				'link' => Model::LINK_HASMANY,
				'on' => 'baseurl'
			),
		);*/

		parent::__construct($key);
	}
	
}
?>
