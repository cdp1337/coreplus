<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminMenuWidget
 *
 * @author powellc
 */
class AdminMenuWidget extends Widget {
	public function execute(){
		$v = $this->_getView();
		
		$pages = PageModel::Find(array('admin' => '1'));
		$v->assignVariable('pages', $pages);
		
		return $v;
	}
}

?>
