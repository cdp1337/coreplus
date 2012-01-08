<?php

/**
 * Description of GettingStartedController
 *
 * @author powellc
 */
class GettingStartedController extends Controller_2_1{
	public function index(){
		$this->setTemplate('/pages/gettingstarted/index.tpl');
		return $this->getView();
	}
	
	public static function _HookCatch404(View $view){
		if(REL_REQUEST_PATH == '/'){
			// Index page was requested! ^_^
			
			// Switch the view's controller with this one.
			$newcontroller = new self();
			// This will allow the system view to be redirected, since I cannot return anything other than a true/false in hook calls.
			$newcontroller->overwriteView($view);
			$view->baseurl = '/GettingStarted';
			$newcontroller->index();
			
			// Prevent event propagation!
			return false;
		}
	}
}

?>
