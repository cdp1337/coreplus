<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Widget
 *
 * @author powellc
 */
class Widget {
	
	protected $_view = null;
	
	/**
	 * Execute this widget and return the View for it.
	 * 
	 * @return View 
	 */
	public function execute(){
		return $this->_getView();
	}
	
	/**
	 * Simple return for if this widget must be instanced to be displayed.
	 * If your widget requires information provided from settings before
	 * rendering can occur, redefine this function in your class and have it
	 * return true.
	 * 
	 * @return boolean
	 */
	public static function MustBeInstanced(){
		return false;
	}
	
	/**
	 * Get the View component for this widget.
	 * 
	 * @return View 
	 */
	protected function _getView(){
		if($this->_view == null){
			$this->_view = new View();
			$this->_view->mode = View::MODE_WIDGET;
			
			// Parse the template for this widget
			$c = get_class($this);
			$c = substr($c, 0, -6); // Should end with 'Widget'
			$this->_view->templatename = 'widgets/' . strtolower($c) . '.tpl';
		}
		
		return $this->_view;
	}
}
