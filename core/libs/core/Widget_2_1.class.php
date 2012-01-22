<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @version 2.1
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU AGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/agpl.html>, 
 * and please contribute back to the community :)
 */

/**
 * Description of Widget
 *
 * @author powellc
 */
class Widget_2_1 {
	
	/**
	 * The request object for the current page.
	 * 
	 * @var PageRequest
	 */
	private $_request = null;
		
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
