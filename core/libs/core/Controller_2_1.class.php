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
	
	public function __construct(){
		// Ensure that some certain data are available from the start.
		//$this->getView();
	}
	
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
	protected function getPageModel(){
		if($this->_model === null){
			$uri = $this->getPageRequest()->uriresolved;
		
			$p = PageModel::Find(array('rewriteurl' => $uri, 'fuzzy' => 0), 1);
		
			// Split this URL, it'll be used somewhere.
			$pagedat = $this->getPageRequest()->splitParts();
		
			if($p){
				// :) Found it
				$this->_model = $p;
			}
			elseif($pagedat){
				// Is this even a valid controller?
				// This will allow a page to be called with it being in the pages database.
				$p = new PageModel();
				$p->set('baseurl', $uri);
				$p->set('rewriteurl', $uri);
				$this->_model = $p;
			}
			else{
				// No page in the database and no valid controller... sigh
				return false;
			}
		
			//var_dump($p); die();
		
			// Make sure all the parameters from both standard GET and core parameters are tacked on.
			if($pagedat && $pagedat['parameters']){
				foreach($pagedat['parameters'] as $k => $v){
					$this->_model->setParameter($k, $v);
				}
			}
			if(is_array($_GET)){
				foreach($_GET as $k => $v){
					if(is_numeric($k)) continue;
					$this->_model->setParameter($k, $v);
				}
			}
		}
		
		return $this->_model;
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
