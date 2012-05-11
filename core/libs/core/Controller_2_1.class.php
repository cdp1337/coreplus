<?php
/**
 * Main Controller parent for the 2.1 API version.
 *
 * @package Core Plus\Core
 * @since 1.9
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


class Controller_2_1 {
	
	/**
	 * The request object for the current page.
	 * 
	 * @var PageRequest
	 */
	private $_request = null;
	
	/**
	 * The page model for the current page.
	 * 
	 * @var PageModel
	 */
	private $_model = null;
	
	/**
	 * The view that gets returned when pages are executed.
	 * 
	 * @var View
	 */
	private $_view = null;
	
	/**
	 * Shared access string for this controller.
	 * 
	 * Optional, if set to non-null, it will be checked before any method is called.
	 * 
	 * @var string
	 */
	public $accessstring = null;
	
	
	/**
	 * Get the page request for the current page.
	 * 
	 * @return PageRequest
	 */
	protected function getPageRequest(){
		if($this->_request === null){
			$this->_request = PageRequest::GetSystemRequest();
		}
		return $this->_request;
	}
	
	/**
	 * Get the view for this controller.
	 * Up to the extending Controller to use this object is it wishes.
	 * 
	 * @return View 
	 */
	public function getView(){
		if($this->_view === null){
			$this->_view = new View();
			$this->_view->baseurl = $this->getPageRequest()->getBaseURL();
		}
		
		return $this->_view;
	}
	
	/**
	 * Replace this controller's view with a different one.
	 * 
	 * This is useful for controllers that intercept a page request and replace their own content.
	 * 
	 * @param View $newview 
	 */
	protected function overwriteView($newview){
		// Reset some of the new view's information.
		$newview->error = View::ERROR_NOERROR;
		
		$this->_view = $newview;
	}
	
	/**
	 * Get the page model for the current page.
	 * 
	 * @return PageModel
	 */
	public function getPageModel(){
		return $this->getPageRequest()->getPageModel();
	}
	
	
	/**
	 * Set the access string for this view and do the access checks against the
	 * currently logged in user.
	 * 
	 * Will also set the access string on the PageModel, since it needs to be reflected in the database.
	 * 
	 * @since 2012.01
	 * @version 2.1
	 * @param string $accessstring
	 * @return boolean True or false based on access for current user.
	 */
	protected function setAccess($accessstring){
		// Update the model
		$this->getPageModel()->set('access', $accessstring);
		
		return(\Core\user()->checkAccess($accessstring));
	}
	
	/**
	 * Set the content of the view being returned.
	 * 
	 * Important for JSON, XML, and other types.
	 * 
	 * @param string $ctype 
	 */
	protected function setContentType($ctype){
		$this->getView()->contenttype = $ctype;
	}
	
	protected function setTemplate($template){
		$this->getView()->templatename = $template;
	}
	
	
	
	/**
	 * Return a valid Controller.
	 * 
	 * This is used because new $pagedat['controller'](); cannot provide typecasting :p
	 * 
	 * @param string $name
	 * @return Controller_2_1
	 */
	public static function Factory($name){
		return new $name();
	}

}
