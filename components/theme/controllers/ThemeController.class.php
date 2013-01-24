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

		// Get the page templates that have widgetareas defined within.
		$components = Core::GetComponents();
		$pagetemplates = array();

		foreach($components as $c) {
			/** @var $c Component_2_1 */
			$dir = $c->getViewSearchDir();
			if(!$dir) continue;

			$dirlen = strlen($dir);
			$component = $c->getName();

			$dh = new Directory_local_backend($dir);
			$pagetplfiles = $dh->ls('tpl', true);

			// not sure why getFilename(path) isn't working as expected, but this works too.
			foreach($pagetplfiles as $obj){
				/** @var $obj File_local_backend */
				$file = substr($obj->getFilename(), $dirlen);

				// Since this is a template, it may actually be in a different location than where the package maintainer put it.
				// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
				$resolved = Template::ResolveFile($file);
				$newobj = new File_local_backend($resolved);

				// Check the contents of the file and see if there is a {widgetarea...} here.
				$contents = $newobj->getContents();
				if(strpos($contents, '{widgetarea') !== false){
					$haswidgets = true;
				}
				else{
					$haswidgets = false;
				}


				$pagetemplates[] = array(
					'file' => $file,
					'resolved' => $resolved,
					'obj' => $newobj,
					'haswidgets' => $haswidgets,
					'component' => $component,
				);
			}
		}

		// The CSS files for the current theme and every component.
		// This is keyed by the filename so that duplicate entries don't show up more than once,
		// ie: if an asset exists in both the component and the theme.
		$cssfiles = array();

		// Give me the current theme!
		$dir = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/assets';
		$dirlen = strlen($dir);
		$component = 'Theme/' . ConfigHandler::Get('/theme/selected');
		$dh = new Directory_local_backend($dir);
		$cssls = $dh->ls('css', true);
		foreach($cssls as $obj){
			/** @var $obj File_local_backend */
			$file = 'assets' . substr($obj->getFilename(), $dirlen);

			// Since this is a template, it may actually be in a different location than where the package maintainer put it.
			// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
			$newobj = \Core\file($file);

			$cssfiles[$file] = array(
				'file' => $file,
				'obj' => $newobj,
				'component' => $component,
			);
		}

		// And the rest of the components... I suppose
		foreach($components as $c) {
			// Now, give me all this component's CSS files!
			$dir = $c->getAssetDir();
			if(!$dir) continue;

			$dirlen = strlen($dir);
			$component = $c->getName();

			$dh = new Directory_local_backend($dir);
			$cssls = $dh->ls('css', true);

			// not sure why getFilename(path) isn't working as expected, but this works too.
			foreach($cssls as $obj){
				/** @var $obj File_local_backend */
				$file = 'assets' . substr($obj->getFilename(), $dirlen);

				// Since this is a template, it may actually be in a different location than where the package maintainer put it.
				// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
				$newobj = \Core\file($file);

				$cssfiles[$file] = array(
					'file' => $file,
					'obj' => $newobj,
					'component' => $component,
				);
			}
		}


		$view->assign('pages', $pagetemplates);
		$view->assign('css', $cssfiles);
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

		// If the browser prefers JSON data, send that.
		if($request->prefersContentType(View::CTYPE_JSON)){
			$view->contenttype = View::CTYPE_JSON;
		}

		// Validate
		if(!\Theme\validate_theme_name($theme)){
			$this->_sendError('Invalid theme requested');
			return;
		}
		
		if(!\Theme\validate_template_name($theme, $template)){
			$this->_sendError('Invalid template requested');
			return;
		}

		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::GetCurrentSiteID()){
			$config_default  = ConfigHandler::GetConfig('/theme/default_template');
			$config_selected = ConfigHandler::GetConfig('/theme/selected');

			if($config_default->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				$this->_sendError('Unable to set the default template on a child site, please ensure that the "/theme/default_template" config is set to be overrideable!');
				return;
			}
			if($config_selected->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				$this->_sendError('Unable to set the selected theme on a child site, please ensure that the "/theme/selected" config is set to be overrideable!');
				return;
			}
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
		$request = $this->getPageRequest();
		$filename = null;

		// Traditional template management
		$template = $request->getParameter('template');
		if($template){
			$t = $request->getParameter(0);

			// Validate
			if(!\Theme\validate_theme_name($t)){
				Core::SetMessage('Invalid theme requested', 'error');
				Core::Redirect('/theme');
			}

			$filename = ROOT_PDIR . 'themes/' . $t . '/skins/' . $template;

			if(!\Theme\validate_template_name($t, $template)){
				Core::SetMessage('Invalid template requested', 'error');
				Core::Redirect('/theme');
			}
		}

		// New page management
		$page = $request->getParameter('page');
		if($page){
			$filename = Template::ResolveFile($page);
			$t = null;
		}

		
		if($request->isPost()){

			$counter = 0;
			foreach($_POST['widgetarea'] as $id => $dat){
				
				// Merge in the global information for this request
				if($template){
					$dat['theme'] = $t;
					$dat['template'] = $template;
				}
				elseif($page){
					$dat['page'] = $page;
				}
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
		// @todo It might make sense to move this into Theme class at some point.
		$tplcontents = file_get_contents($filename);
		preg_match_all("/\{widgetarea.*name=[\"'](.*)[\"'].*\}/isU", $tplcontents, $matches);

		// I need to assemble a list of baseurls to search for as well.
		// this is because if a page has baseurl="/abc", I don't want to display
		// widgets for installable="/def".
		$installables = array(''); // Start with empty, they can go anywhere.
		foreach($matches[0] as $str){
			if(!strpos($str, 'baseurl')) continue;

			$baseurl = preg_replace('/.*baseurl="([^"]*)".*/', '$1', $str);
			// This is required because matches can be fuzzy, probably won't, but can be.
			// ie: /user-social/view/`$user->get('id')` probably won't be used to mark as installable,
			// but /user-social/view might be.
			while($baseurl){
				$installables[] = $baseurl;
				if(strpos($baseurl, '/') === false) break;
				$baseurl = substr($baseurl, 0, strrpos($baseurl, '/'));
			}
		}
		
		// These are all the available widgets on the site otherwise.
		$widgetfactory = new ModelFactory('WidgetModel');
		$widgetfactory->order('title');
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$where = new DatasetWhereClause();
			$where->setSeparator('OR');
			$where->addWhere('site = -1');
			$where->addWhere('site = ' . MultiSiteHelper::GetCurrentSiteID());
			$widgetfactory->where($where);
		}
		if(sizeof($installables)){
			$where = new DatasetWhereClause();
			$where->setSeparator('OR');
			foreach($installables as $baseurl){
				$where->addWhereParts('installable', '=', $baseurl);
			}
			$widgetfactory->where($where);
		}

		$widgets = $widgetfactory->get();
		
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
				$baseurl = $wi->get('baseurl');
				$title = isset($widgetnames[ $baseurl ]) ? $widgetnames[ $baseurl ] : null;
				// All I need is the name and metadata, TYVM.
				$instancewidgets[] = array(
					'title' => $title,
					'baseurl' => $baseurl,
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
		if($template){
			$view->title = 'Widgets on ' . $t . '-' . $template;
		}
		elseif($page){
			$view->title = 'Widgets for ' . $page;
		}

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

	public function editor(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		if($request->getParameter('css')){
			$file = $request->getParameter('css');
			$filename = $file;
			$activefile = 'style';
		}
		elseif($request->getParameter('tpl')) {
			$file = $request->getParameter('tpl');
			$filename = Template::ResolveFile($file);
			$activefile = 'template';
		}
		elseif($request->getParameter('skin')) {
			$file = $request->getParameter('skin');
			$filename = ROOT_PDIR . $file; // Simple enough.
			$activefile = 'skin';
		}
		else {
			//no special gets...
			// This version of the editor doesn't support viewing without any file specified.
			Core::SetMessage('No file requested', 'error');
			Core::Redirect('/theme');
		}


		$fh = new File_local_backend($filename);
		$content = $fh->getContents();
		$basename = $fh->getBasename();

		$m = new ThemeEditorItemModel();
		$m->set('content', $content);
		$m->set('filename', $file);

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ThemeController::_SaveEditorHandler');
		// I need to add the file as a system element so core doesn't try to reuse the same forms on concurrent edits.
		$form->addElement('system', array('name' => 'file', 'value' => $file));
		$form->addElement('system', array('name' => 'filetype', 'value' => $activefile));

		$form->addElement('submit', array('value' => 'Update'));

		$revisions = ThemeEditorItemModel::Find( array('filename' => $fh->getFilename()), 5, 'updated DESC');

		$view->assignVariable('activefile', $activefile);
		$view->assignVariable('form', $form);
		$view->assignVariable('content', $content);
		$view->assignVariable('filename', $basename);
		$view->assignVariable('revisions', $revisions);

		//$view->addBreadcrumb('Theme Manager', '/theme');
		$view->title = 'Editor';
	}

	/**
	 * Helper function for the setdefault method.
	 * @param $message
	 */
	private function _sendError($message){
		$request = $this->getPageRequest();
		$view = $this->getView();

		if($request->prefersContentType(View::CTYPE_JSON)){
			$view->jsondata = array('message' => $message, 'status' => 0);
		}
		else{
			Core::SetMessage($message, 'error');
			Core::Redirect('/theme');
		}
	}
	
	public static function Widgets_Save(View $view){
	var_dump(CurrentPage::Singleton());
		var_dump($view); die();
	}

	public static function _SaveEditorHandler(Form $form){
		$newmodel = $form->getModel();
		$file = $form->getElement('file')->get('value');
		$activefile = $form->getElement('filetype')->get('value');
		// The inbound file types depends on how to read the file.
		switch($activefile){
			case 'skin':
			case 'style':
				$filename = $file;
				break;
			case 'template':
				$filename = Template::ResolveFile($file);
				break;
			default:
				Core::SetMessage('Unsupported file type: ' . $activefile, 'error');
				return false;
		}

		$fh = new File_local_backend($filename);

		// Check and see if they're the same, ie: no change.  I don't want to create a bunch of moot revisions.
		if($newmodel->get('content') == $fh->getContents()){
			Core::SetMessage('Cowardly refusing to save a file with no changes.', 'info');
			return '/theme';
		}

		$model = new ThemeEditorItemModel();
		$model->set('filename', $file);
		// Remember, I'm setting the contents of the versioned file into the database, NOT the new ones.
		$model->set('updated', $fh->getMTime());
		$model->set('content', $fh->getContents());
		$model->save();

		// What happens now is based on the type of the inbound file.
		switch($activefile){
			case 'skin':
				// Just replace the contents of that file.  No theme versioning allowed.
				$fh->putContents($newmodel->get('content'));
				break;
			case 'template':
				// This gets written into the current theme directory.
				$themefh = new File_local_backend(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $file);
				$themefh->putContents($newmodel->get('content'));
				break;
			case 'style':
				// This gets written into the current theme directory.
				$themefh = new File_local_backend(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $file);
				$themefh->putContents($newmodel->get('content'));

				// This is required to get assets updated to the CDN correctly.
				$theme = ThemeHandler::GetTheme();
				$hash = $themefh->getHash();
				$theme->addAssetFile(array('file' => $file, 'md5' => $hash));
				$theme->save();
				$theme->reinstall();
			default:
		}

		Core::SetMessage('Updated file successfully', 'success');
		return '/theme';
	}
}
