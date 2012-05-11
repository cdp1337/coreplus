<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

/**
 * Description of Widget
 *
 * @author powellc
 */
class Widget {
	
	protected $_view = null;
	
	/**
	 * The widget model for this widget.
	 * Will only have this if this widget is instanced from the admin.
	 * 
	 * @var WidgetModel
	 */
	protected $_model = null;
	
	/**
	 * Execute this widget and return the View for it.
	 * 
	 * @return View 
	 */
	public function execute(){
		
		// I need to do this to actually resolve the calling widget.
		$c = get_called_class();
		if($this->_model === null && $c::MustBeInstanced()){
			throw new WidgetException($c . ' must be instanced before it can be executed.');
		}
		
		return $this->_getView();
	}
	
	
	/**
	 * Set the corresponding model for this widget, and consequently set the
	 * widget to be instanced.
	 * 
	 * @param WidgetModel $model 
	 */
	public function setModel(WidgetModel $model){
		$this->_model = $model;
	}
	
	
	//// PROTECTED METHODS \\\\
	
	
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
	
	protected function _getSettings(){
		if($this->_model === null) return null;
		else return json_decode($this->_model->get('settings'), true);
	}
	
	//// STATIC PUBLIC METHODS \\\\
	
	
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
}

class WidgetException extends Exception{
	
}