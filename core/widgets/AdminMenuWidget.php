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
		$viewable = array();
		foreach($pages as $p){
			if(!Core::User()->checkAccess($p->get('access'))) continue;
			
			$viewable[] = $p;
		}
		$v->assignVariable('pages', $viewable);
		
		return $v;
	}
}

?>
