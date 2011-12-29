<?php

class CoreController extends Controller{

	public static function GetJSLibrary(View $view){
		$lib = $view->getParameter(0);
		
		if(!ComponentHandler::LoadScriptLibrary($lib)){
			die('foo :( ');
		}
		var_dump(CurrentPage::Singleton()); die();
	}
}
