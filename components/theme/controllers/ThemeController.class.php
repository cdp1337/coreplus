<?php

require_once(ROOT_PDIR . 'components/theme/functions/common.php');

/**
 * The main theme administration controller.
 * 
 * This is reponsible for all administrative tasks associated with the theme. 
 */
class ThemeController extends Controller_2_1{
	
	public function __construct() {
		$this->accessstring = 'g:admin';
	}
	
	/**
	 * View to display a list of currently installed themes, their templates, and be able to manage
	 * their templates and set them as default.
	 * 
	 * @todo Implement an Add/Upload Theme link on this page.
	 */
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
		$view->title = 'Theme Manager';
	}
	
	/**
	 * Set a requested theme and template as default for the site.
	 */
	public function setdefault(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		
		$theme = $this->getPageRequest()->getParameter(0);
		$template = $this->getPageRequest()->getParameter('template');
		
		// Validate
		if(!\Theme\validate_theme_name($theme)){
			Core::SetMessage('Invalid theme requested', 'error');
			Core::Redirect('/Theme');
		}
		
		if(!\Theme\validate_template_name($theme, $template)){
			Core::SetMessage('Invalid template requested', 'error');
			Core::Redirect('/Theme');
		}
		
		if($request->isPost()){
			
			ConfigHandler::Set('/theme/default_template', $template);
			ConfigHandler::Set('/theme/selected', $theme);
			
			Core::SetMessage('Updated default theme', 'success');
			
			// If the browser prefers JSON data, send that.
			if($request->prefersContentType(View::CTYPE_JSON)){
				$view->contenttype = View::CTYPE_JSON;
				$view->jsondata = array('message' => 'Updated default theme', 'status' => 1);
			}
			else{
				Core::Redirect('/Theme');
			}
		}
		
		$view->assign('theme', $theme);
		$view->assign('template', $template);
	}
	
	public function widgets(){
		$view = $this->getView();
		
		$t = $this->getPageRequest()->getParameter(0);
		
		// Validate
		if(!\Theme\validate_theme_name($t)){
			Core::SetMessage('Invalid theme requested', 'error');
			Core::Redirect('/Theme');
		}
		
		$template = $this->getPageRequest()->getParameter('template');
		// @todo Add support for page-specific configuration.
		
		$filename = ROOT_PDIR . 'themes/' . $t . '/skins/' . $template;
		
		if(!\Theme\validate_template_name($t, $template)){
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
                $dat['access'] = $dat['widgetaccess'];
				
				if(strpos($id, 'new') !== false){
					$w = new WidgetInstanceModel();
					$w->setFromArray($dat);
					$w->save();
				}
				elseif(strpos($id, 'del-') !== false){
					$w = new WidgetInstanceModel(substr($id, 4));
					$w->delete();
					// Reset the counter back down one notch since this was a deletion request.
					--$counter;
				}
				else{
					$w = new WidgetInstanceModel($id);
					$w->setFromArray($dat);
					$w->save();
				}
			} // foreach($_POST['widgetarea'] as $id => $dat)
		} // if($this->getPageRequest()->isPost())
		
		// Get a list of the widgetareas on the theme.
		// These are going to be {widgetarea} tags.
		// @todo It might make sense to move this into Theme classs at some point.
		$tplcontents = file_get_contents($filename);
		preg_match_all("/\{widgetarea.*name=[\"'](.*)[\"'].*\}/isU", $tplcontents, $matches);
		
		
		// These are all the available widgets on the site otherwise.
		$widgets = WidgetModel::Find(null, null, 'title');
		
		// This is a lookup of widget titles to URL, since the title is derived from the widget's controller and
		// saved in the widget table separate from the instances.
		$widgetnames = array();
		foreach($widgets as $widget){
			$widgetnames[$widget->get('baseurl')] = $widget->get('title');
		}

		$areas = array();
		foreach($matches[1] as $v){
			$instancewidgets = array();
			$wifac = WidgetInstanceModel::Find(array('theme' => $t, 'template' => $template, 'widgetarea' => $v), null, 'weight');
			foreach($wifac as $wi){
				// All I need is the name and metadata, TYVM.
				$instancewidgets[] = array(
					'title' => $widgetnames[$wi->get('baseurl')],
					'baseurl' => $wi->get('baseurl'),
					'id' => $wi->get('id'),
					'access' => $wi->get('access')
				);
			}
			
			$areas[] = array('name' => $v, 'instances' => $instancewidgets);
		}
		
		
		
		$view->assign('widget_areas', $areas);
		$view->assign('widgets', $widgets);
		$view->assign('theme', $t);
		$view->assign('template', $template);
		$view->title = 'Widgets on ' . $t . '-' . $template;
		//$view->addBreadcrumb($view->title);
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
