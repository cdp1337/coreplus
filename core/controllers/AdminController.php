<?php

class AdminController extends Controller {
	public static function Menu(View $page){
		$pages = PageModel::Find(array('admin' => '1'));
		$page->assignVariable('pages', $pages);
	}
	
	public static function ReinstallAll(View $page){
		// Just run through every component currently installed and reinstall it.
		// This will just ensure that the component is up to date and correct as per the component.xml metafile.
		
		$changes = array();
		
		foreach(ThemeHandler::GetAllThemes() as $t){
			if(!$t->isInstalled()) continue;
			
			if($t->reinstall()){
				$changes[] = 'Reinstalled theme ' . $t->getName();
			}
		}
		
		foreach(ComponentHandler::GetAllComponents() as $c){
			if(!$c->isInstalled()) continue;
			
			$c->reinstall();
			$changes[] = 'Reinstalled component ' . $c->getName();
		}
		
		// Flush the system cache, just in case
		Core::Cache()->flush();
		
		//$page->title = 'Reinstall All Components';
		$page->access = 'g:admin';
		$page->assign('changes', $changes);
	}
	
	//public static function Edit()
}
