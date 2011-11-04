<?php

class ThemeController extends Controller{
	
	public static $AccessString = 'g:admin';
	
	public static function Index(View $view){
		$default = ConfigHandler::Get('/theme/selected');
		
		$themes = array();
		$dir = ROOT_PDIR . 'themes';
		$dh = opendir($dir);
		if($dh){
			while(($file = readdir($dh)) !== false){
				if($file{0} == '.') continue;
				if(!is_dir($dir . '/' . $file)) continue;
				
				$themes[] = array(
					'name' => $file,
					'default' => ($default == $file),
					'templates' => ThemeHandler::GetTheme($file)->getTemplates(),
				);
			}
			closedir($dh);
		}
		
		/*if(sizeof($themes) == 1){
			Core::Redirect('/Theme/View/' . $themes[0]['name']);
		}*/
		
		
		$view->assign('themes', $themes);
	}
	
	
	public static function Widgets(View $view){
		$t = $view->getParameter(0);
		if(!$t || $t{0} == '.' || strpos($t, '..') !== false || !is_dir(ROOT_PDIR . 'themes/' . $t)){
			Core::SetMessage('Invalid theme requested', 'error');
			Core::Redirect('/Theme');
		}
		
		$template = $view->getParameter('template');
		// @todo Add support for page-specific configuration.
		
		$filename = ROOT_PDIR . 'themes/' . $t . '/' . $template;
		
		if($template{0} == '.' || !$template || !is_readable($filename)){
			Core::SetMessage('Invalid template requested', 'error');
			Core::Redirect('/Theme');
		}
		
		
		// Get a list of the widgetareas on the theme.
		// These are going to be {widgetarea} tags.
		// @todo It might make sense to move this into Theme classs at some point.
		$tplcontents = file_get_contents($filename);
		preg_match_all("/\{widgetarea.*name=[\"'](.*)[\"'].*\}/isU", $tplcontents, $matches);

		$areas = array();
		foreach($matches[1] as $v){
			$areas[] = $v;
		}
		
		$widgets = WidgetModel::Find(null, null, 'title');
		
		$view->assign('widget_areas', $areas);
		$view->assign('widgets', $widgets);
		
		return;
		
		var_dump($widgets);
		die('Yet to be completed...');
		$widgets = array();
		foreach(ComponentHandler::GetLoadedWidgets() as $w){
			var_dump($w);
			//if()
			//var_dump($w::MustBeInstanced());
		}
		
	}
}

?>
