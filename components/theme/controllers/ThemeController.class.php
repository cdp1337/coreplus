<?php

class ThemeController extends Controller_2_1{
	
	public function __construct() {
		$this->accessstring = 'g:admin';
	}
	
	public function index(){
		$view = $this->getView();
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
	
	
	public function widgets(){
		$view = $this->getView();
		
		$t = $this->getPageRequest()->getParameter(0);
		if(!$t || $t{0} == '.' || strpos($t, '..') !== false || !is_dir(ROOT_PDIR . 'themes/' . $t)){
			Core::SetMessage('Invalid theme requested', 'error');
			Core::Redirect('/Theme');
		}
		
		$template = $this->getPageRequest()->getParameter('template');
		// @todo Add support for page-specific configuration.
		
		$filename = ROOT_PDIR . 'themes/' . $t . '/' . $template;
		
		if($template{0} == '.' || !$template || !is_readable($filename)){
			Core::SetMessage('Invalid template requested', 'error');
			Core::Redirect('/Theme');
		}
		
		if($this->getPageRequest()->isPost()){
			
			$counter = 0;
			foreach($_POST['widgetarea'] as $id => $dat){
				
				// Merge in the global information for this request
				$dat['theme'] = $t;
				$dat['template'] = $template;
				$dat['weight'] = ++$counter;
				
				if(strpos($id, 'new') !== false) $w = new WidgetInstanceModel();
				else $w = new WidgetInstanceModel($id);
				
				$w->setFromArray($dat);
				$w->save();
			}
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
		$view->assign('theme', $t);
		$view->assign('template', $template);
		
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
	
	public function widgets_Add(){
		$view = $this->getView();
				
		$widgets = array();
		foreach(ComponentHandler::GetLoadedWidgets() as $w){
			$widgets[] = $w;
		}
		
		$view->assign('widget_classes', $widgets);
		
		if($view->request['method'] == View::METHOD_POST){
			$w = $_POST['widgetclass'];
			if(!$w){
				Core::SetMessage('No widget type requested, please select one.', 'error');
				return;
			}
			if(!is_subclass_of($w, 'Widget') ){
				Core::SetMessage('Invalid widget requested', 'error');
				return;
			}
			
			$title = $_POST['title'] ? $_POST['title'] : 'New ' . $w;
			
			// Save this widget into the database.
			$m = new WidgetModel();
			$m->set('class', $w);
			$m->set('title', $title);
			$m->save();
			
			if($w::MustBeInstanced()){
				// This widget requires additional settings in order for it to be instantiated.
				// Redirect to the edit page.
				Core::SetMessage('Created widget, please configure it.', 'info');
				Core::Redirect('/Theme/Widgets/Edit/' . $m->get('id'));
			}
			else{
				// Doesn't need instantiated, can be used as is.
				Core::SetMessage('Created widget.', 'success');
				Core::Redirect(Core::GetNavigation('/Theme/Widgets'));
			}
		}
	}
	
	public static function Widgets_Save(View $view){
	var_dump(CurrentPage::Singleton());
		var_dump($view); die();
	}
}

?>
