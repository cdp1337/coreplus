<?php
// @todo 2012.05.11 cpowell - Can I kill this file?  It doesn't seem to be doing anything.

class CoreController extends Controller{

	public static function GetJSLibrary(View $view){
		$lib = $view->getParameter(0);
		
		if(!ComponentHandler::LoadScriptLibrary($lib)){
			die('foo :( ');
		}
		var_dump(CurrentPage::Singleton()); die();
	}
}
