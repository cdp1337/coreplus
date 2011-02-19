<?php

class AdminController extends Controller {
	public static function Menu(View $page){
		$pages = PageModel::Find(array('admin' => '1'));
		$page->assignVariable('pages', $pages);
	}
	
	public static function ReinstallAll(View $page){
		// Just run through every component currently installed and reinstall it.
		// This will just ensure that the component is up to date and correct as per the component.xml metafile.
		
		foreach(ComponentHandler::GetAllComponents() as $c){
			if(!$c->isInstalled()) continue;
			
			$c->reinstall();
		}
		
		// @todo Have some feedback or notification to the user...
		// @todo Make the template too....
	}
	
	//public static function Edit()
}
