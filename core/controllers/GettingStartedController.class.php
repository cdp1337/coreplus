<?php

/**
 * Description of GettingStartedController
 *
 * @author powellc
 */
class GettingStartedController extends Controller{
	public static function Index(View $view){
		
	}
	
	public static function _HookCatch404($view){
		if(REL_REQUEST_PATH == ''){
			// Index page was requested! ^_^
			$p = new PageModel('/GettingStarted');
			$p->hijackView($view);
			GettingStartedController::Index($view);
			
			// Prevent event propagation!
			return false;
		}
	}
}

?>
