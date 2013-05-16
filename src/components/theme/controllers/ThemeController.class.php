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
	 * Page to display the currently installed themes and shortcuts to various operations therein.
	 */
	public function index(){
		$view = $this->getView();
		$selected = ConfigHandler::Get('/theme/selected');

		$themes = ThemeHandler::GetAllThemes();
		$current = ThemeHandler::GetTheme($selected);

		// The source objects to look for assets in.
		// Set initially to all the installed components.
		$assetsources = Core::GetComponents();
		// And add on the current theme.
		$assetsources[] = $current;

		// Load in all asset files available from the installed components and current theme.
		// these are assembled into a virtual directory listing.
		$assets = array();

		// Give me the current theme!
		foreach($assetsources as $source) {
			$dir = $source->getAssetDir();
			if(!$dir) continue;

			$dirlen = strlen($dir);
			$name = $source->getName();

			$dh = new Directory_local_backend($dir);
			$ls = $dh->ls(null, true);
			foreach($ls as $obj){
				// Skip directories.
				if(!$obj instanceof File_local_backend) continue;

				/** @var $obj File_local_backend */
				$file = 'assets/' . substr($obj->getFilename(), $dirlen);

				// Since this is a template, it may actually be in a different location than where the package maintainer put it.
				// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
				$newobj = \Core\file($file);

				$assets[$file] = array(
					'file' => $file,
					'obj' => $newobj,
					'component' => $name,
				);
			}
		}

		// Now that the asset files have been loaded into a flat array, I need to convert that to the properly nested version.
		ksort($assets);
		$nestedassets = array();
		foreach($assets as $k => $obj){
			$parts = explode('/', $k);
			$lastkey = sizeof($parts) - 1;
			$thistarget =& $nestedassets;
			foreach($parts as $i => $bit){
				if($i == $lastkey){
					$thistarget[$bit] = $obj;
				}
				else{
					if(!isset($thistarget[$bit])){
						$thistarget[$bit] = [];
					}
					$thistarget =& $thistarget[$bit];
				}
			}
		}


		$view->title = 'Theme Manager';
		$view->assign('themes', $themes);
		$view->assign('current', $current);
		$view->assign('assets', $nestedassets);
	}
	
	/**
	 * View to display a list of currently installed themes, their templates, and be able to manage
	 * their templates and set them as default.
	 * 
	 * @todo Implement an Add/Upload Theme link on this page.
	 */
	public function index2(){
		$view = $this->getView();
		$default = ConfigHandler::Get('/theme/selected');
		
		$themes = array();
		$dir = ROOT_PDIR . 'themes';
		$dh = opendir($dir);
		if($dh){
			while(($file = readdir($dh)) !== false){
				if($file{0} == '.') continue;
				if(!is_dir($dir . '/' . $file)) continue;

				// Load up the templates for this theme.
				$templates = ThemeHandler::GetTheme($file)->getTemplates();

				$themes[] = array(
					'name' => $file,
					'default' => ($default == $file),
					'templates' => $templates,
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
				$resolved = Core\Templates\Template::ResolveFile($file);
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
		
		$themename = $this->getPageRequest()->getParameter(0);
		$template = $this->getPageRequest()->getParameter('template');

		// If the browser prefers JSON data, send that.
		if($request->prefersContentType(View::CTYPE_JSON)){
			$view->contenttype = View::CTYPE_JSON;
		}

		// Validate the theme name
		if(!\Theme\validate_theme_name($themename)){
			Core::SetMessage('Invalid theme requested', 'error');
			Core::GoBack();
		}

		$theme = ThemeHandler::GetTheme($themename);


		if($template){
			// The template itself can be ignored.
			if(!\Theme\validate_template_name($themename, $template)){
				Core::SetMessage('Invalid template requested', 'error');
				Core::GoBack();
			}
		}
		else{
			// and the default one is used otherwise.
			$allskins = $theme->getSkins();
			$template = $allskins[0]['file'];
		}


		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::GetCurrentSiteID()){
			$config_default  = ConfigHandler::GetConfig('/theme/default_template');
			$config_selected = ConfigHandler::GetConfig('/theme/selected');

			if($config_default->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				Core::SetMessage('Unable to set the default template on a child site, please ensure that the "/theme/default_template" config is set to be overrideable!', 'error');
				Core::GoBack();
			}
			if($config_selected->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				Core::SetMessage('Unable to set the selected theme on a child site, please ensure that the "/theme/selected" config is set to be overrideable!', 'error');
				Core::GoBack();
			}
		}

		if($request->isPost()){

			if($themename != ConfigHandler::Get('/theme/selected')){
				// The theme changed, change the admin skin too!
				ConfigHandler::Set('/theme/default_admin_template', $template);
				// And the email skin.
				ConfigHandler::Set('/theme/default_email_template', '');
			}
			ConfigHandler::Set('/theme/default_template', $template);
			ConfigHandler::Set('/theme/selected', $themename);
			
			Core::SetMessage('Updated default theme', 'success');
			Core::GoBack();
		}
		
		$view->assign('theme', $themename);
		$view->assign('template', $template);
	}

	/**
	 * Set a given skin for default use on admin pages.
	 *
	 * Will NOT affect the theme selected.
	 */
	public function setadmindefault(){
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
			Core::SetMessage('Invalid theme requested', 'error');
			Core::GoBack();
		}

		if(!\Theme\validate_template_name($theme, $template)){
			Core::SetMessage('Invalid template requested', 'error');
			Core::GoBack();
		}

		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::GetCurrentSiteID()){
			$config_default  = ConfigHandler::GetConfig('/theme/default_admin_template');

			if($config_default->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				Core::SetMessage('Unable to set the default template on a child site, please ensure that the "/theme/default_template" config is set to be overrideable!', 'error');
				Core::GoBack();
			}
		}

		if($request->isPost()){

			if($theme != ConfigHandler::Get('/theme/selected')){
				Core::SetMessage('The admin skin must be on the same theme as the site!', 'error');
				Core::GoBack();
			}
			ConfigHandler::Set('/theme/default_admin_template', $template);

			Core::SetMessage('Updated admin skin', 'success');
			Core::GoBack();
		}
		else{
			return View::ERROR_BADREQUEST;
		}
	}


	/**
	 * Set a given skin for default use on email communications.
	 *
	 * Will NOT affect the theme selected.
	 */
	public function setemaildefault(){
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
			Core::SetMessage('Invalid theme requested', 'error');
			Core::GoBack();
		}

		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::GetCurrentSiteID()){
			$config_default  = ConfigHandler::GetConfig('/theme/default_email_template');

			if($config_default->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				Core::SetMessage('Unable to set the default template on a child site, please ensure that the "/theme/default_email_template" config is set to be overrideable!', 'error');
				Core::GoBack();
			}
		}

		if($request->isPost()){

			if($theme != ConfigHandler::Get('/theme/selected')){
				Core::SetMessage('The admin skin must be on the same theme as the site!', 'error');
				Core::GoBack();
			}
			ConfigHandler::Set('/theme/default_email_template', $template);

			Core::SetMessage('Updated email skin', 'success');
			Core::GoBack();
		}
		else{
			return View::ERROR_BADREQUEST;
		}
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
			$filename = Core\Templates\Template::ResolveFile($page);
			$t = null;
		}

		
		if($request->isPost()){

			$counter = 0;
			$changes = ['created' => 0, 'updated' => 0, 'deleted' => 0];

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
					$changes['created']++;
				}
				elseif(strpos($id, 'del-') !== false){
					$w = new WidgetInstanceModel(substr($id, 4));
					$w->delete();
					// Reset the counter back down one notch since this was a deletion request.
					--$counter;
					$changes['deleted']++;
				}
				else{
					$w = new WidgetInstanceModel($id);
					$w->setFromArray($dat);
					if($w->save()) $changes['updated']++;
				}
			} // foreach($_POST['widgetarea'] as $id => $dat)

			// Display some human friendly status message.
			if($changes['created'] || $changes['updated'] || $changes['deleted']){
				$changetext = [];

				if($changes['created'] == 1) $changetext[] = 'One widget added';
				elseif($changes['created'] > 1) $changetext[] = $changes['created'] . ' widgets added';

				if($changes['updated'] == 1) $changetext[] = 'One widget updated';
				elseif($changes['updated'] > 1) $changetext[] = $changes['updated'] . ' widgets updated';

				if($changes['deleted'] == 1) $changetext[] = 'One widget deleted';
				elseif($changes['deleted'] > 1) $changetext[] = $changes['deleted'] . ' widgets deleted';

				Core::SetMessage(implode('<br/>', $changetext), 'success');
			}
			else{
				Core::SetMessage('No changes performed', 'info');
			}

			Core::Reload();
		} // if($this->getPageRequest()->isPost())
		
		// Get a list of the widgetareas on the theme.
		// These are going to be {widgetarea} tags.
		// @todo It might make sense to move this into Theme class at some point.
		$tplcontents = file_get_contents($filename);
		preg_match_all("/\{widgetarea.*name=[\"'](.*)[\"'].*\}/isU", $tplcontents, $matches);

		// I need to assemble a list of installables to search for as well.
		// this is because if a page has installable="/abc", I don't want to display
		// widgets for installable="/def".
		$installables = array(''); // Start with empty, they can go anywhere.
		foreach($matches[0] as $str){
			if(!strpos($str, 'installable')) continue;

			$baseurl = preg_replace('/.*installable="([^"]*)".*/', '$1', $str);
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

		if(sizeof($installables) > 1){
			$where = new DatasetWhereClause();
			$where->setSeparator('OR');
			foreach($installables as $baseurl){
				$where->addWhereParts('installable', '=', $baseurl);
			}
			$widgetfactory->where($where);
		}
		else{
			$widgetfactory->where('installable = ');
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
			if($page){
				$wifac = WidgetInstanceModel::Find(array('page' => $page, 'widgetarea' => $v), null, 'weight');
			}
			else{
				$wifac = WidgetInstanceModel::Find(array('theme' => $t, 'template' => $template, 'widgetarea' => $v), null, 'weight');
			}

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


		if($request->getParameter('template')){
			// This is the basename of the file, (unresolved)
			// example: "skins/basic.tpl"
			$file = $request->getParameter('template');
			// And the fully resolved one!
			// example: "/home/blah/public_html/themes/awesome-one/skins/basic.tpl"
			$filename = \Core\Templates\Template::ResolveFile($file);
			// This gets resolved automatically.
			$mode = null;
			$activefile = 'template';
		}
		elseif($request->getParameter('file') && strpos($request->getParameter('file'), 'assets/') === 0){
			$file = $request->getParameter('file');
			// Trim off the base of the filename, ("assets/")
			$filename = substr($file, 7);
			// And try to look up and find this damn file...
			$srcdirs = array();
			$srcdirs[] = ROOT_PDIR . 'themes/custom/assets/';
			$srcdirs[] = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/assets/';
			foreach(Core::GetComponents() as $c){
				if($c->getAssetDir()){
					$srcdirs[] = $c->getAssetDir();
				}
			}
			foreach($srcdirs as $dir){
				if(file_exists($dir . $filename)){
					$filename = $dir . $filename;
					break;
				}
			}
			// This gets resolved automatically.
			$mode = null;
			$activefile = 'file';
		}
		else {
			//no special gets...
			// This version of the editor doesn't support viewing without any file specified.
			Core::SetMessage('No file requested', 'error');
			Core::Redirect('/theme');
		}


		$fh = new File_local_backend($filename);
		$customdest = \Core\directory('themes/custom');
		if(!$customdest->isWritable()){
			Core::SetMessage('Directory themes/custom is not writable!  Inline file editing disabled.', 'error');
			Core::GoBack();
		}

		// Lookup the mode.
		if(!$mode){
			switch($fh->getMimetype()){
				case 'text/css':
					$mode = 'css';
					break;
				case 'text/javascript':
					$mode = 'javascript';
					break;
				case 'text/html':
					$mode = 'htmlmixed';
					break;
				default:
					$mode = 'smarty';
					break;
			}
		}

		// @todo Finish this.
		if(strpos($fh->getMimetype(), 'text/') !== 0){
			Core::SetMessage('Sorry, but only text files can be edited right now... Expect this to function soon though ;)', 'info');
			Core::GoBack();
		}


		// Load the last 10 revisions from the database.
		$revisions = ThemeTemplateChangeModel::Find( array('filename' => $file), 10, 'updated DESC');

		$rev = null;
		if($request->getParameter('revision')){
			// Look up that revision.
			$rev = ThemeTemplateChangeModel::Construct($request->getParameter('revision'));
			if($rev->get('filename') == $file){
				$content = $rev->get('content');
				$revision = $rev->get('id');
			}
			else{
				Core::SetMessage('Invalid revision requested!', 'error');
				$rev = null;
			}
		}

		if(!$rev){
			// No revision requested, just pull the contents from the live file
			$content = $fh->getContents();

			if(sizeof($revisions)){
				// Grab the latest one!
				$rev = $revisions[0];
			}
		}

		if($rev && $rev == $revisions[0]){
			$islatest = true;
		}
		elseif(!$rev){
			$islatest = true;
		}
		else{
			$islatest = false;
		}

		$basename = $fh->getBasename();

		$m = new ThemeTemplateChangeModel();
		$m->set('content', $content);
		$m->set('filename', $file);

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ThemeController::_SaveEditorHandler');
		// I need to add the file as a system element so core doesn't try to reuse the same forms on concurrent edits.
		//$form->addElement('system', array('name' => 'revision', 'value' => $revision));
		$form->addElement('system', array('name' => 'file', 'value' => $file));
		$form->addElement('system', array('name' => 'filetype', 'value' => $activefile));

		if(!$islatest){
			$form->addElement('submit', array('value' => 'Update/Revert'));
		}
		else{
			$form->addElement('submit', array('value' => 'Update'));
		}

		// This form needs to load the content live on every pageload!
		// This is because the user can jump between the different versions on-the-fly,
		// so if the form system has its way, it would keep the first in cache and only display that.
		// No cache for you!
		$form->clearFromSession();


		$view->assign('activefile', $activefile);
		$view->assign('form', $form);
		$view->assign('content', $content);
		$view->assign('filename', $basename);
		$view->assign('revisions', $revisions);
		$view->assign('revision', $rev);
		$view->assign('file', $file);
		$view->assign('fh', $fh);
		$view->assign('islatest', $islatest);
		$view->assign('mode', $mode);

		//$view->addBreadcrumb('Theme Manager', '/theme');
		$view->title = 'Editor';
	}

	/**
	 * Page to display a user interface to select the optional stylesheets.
	 */
	public function selectstylesheets(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$file = $request->getParameter('template');
		$tpl = \Core\Templates\Template::Factory($file);
		$stylesheets = $tpl->getOptionalStylesheets();

		$form = new Form();
		foreach($stylesheets as $style){
			$model = TemplateCssModel::Construct($file, $style['src']);
			if(!$model->exists() && isset($style['default']) && $style['default']){
				$model->set('enabled', 1);
			}

			$form->addElement(
				'checkbox',
				[
					'title' => $style['title'],
					'name' => 'stylesheets[]',
					'value' => $style['src'],
					'checked' => $model->get('enabled'),
				]
			);
		}
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Update Stylesheets']);


		// If it was a POST... then save that and go back.
		if($request->isPost()){
			if(!isset($_POST['stylesheets'])) $_POST['stylesheets'] = array();
			// Run through the stylesheets retrieved and save their setting.
			foreach($stylesheets as $style){
				$model = TemplateCssModel::Construct($file, $style['src']);
				$model->set('enabled', (in_array($style['src'], $_POST['stylesheets']) ? 1 : 0));
				$model->save();
			}

			Core::SetMessage('Updated optional stylesheets successfully', 'success');
			Core::GoBack(1);
		}


		//$view->addBreadcrumb('Theme Manager', '/theme');
		$view->title = 'Select Optional Stylesheets';
		$view->assign('file', $file);
		$view->assign('form', $form);
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
			case 'template':
				$filename = \Core\Templates\Template::ResolveFile($file);
				$customfilename = ROOT_PDIR . 'themes/custom/' . $file;
				break;
			case 'file':
				$filename = $file; // It'll get transposed.
				$customfilename = ROOT_PDIR . 'themes/custom/' . $file;
				break;
			default:
				Core::SetMessage('Unsupported file type: ' . $activefile, 'error');
				return false;
		}

		$customfh = \Core\file($customfilename);
		if($customfh->exists()){
			// If the custom one exists... this will be the source file too!
			$sourcefh = $customfh;
		}
		else{
			$sourcefh = new File_local_backend($filename);
		}


		// Check and see if they're the same, ie: no change.  I don't want to create a bunch of moot revisions.
		if($newmodel->get('content') == $sourcefh->getContents()){
			Core::SetMessage('No changes performed.', 'info');
			return '/theme';
		}

		// Before I overwrite this file, check and see if the original has been snapshot first!
		$c = ThemeTemplateChangeModel::Count(['filename = ' . $file]);
		if(!$c){
			$original = new ThemeTemplateChangeModel();
			$original->setFromArray(
				[
					'comment' => 'Original File',
					'filename' => $file,
					'content' => $sourcefh->getContents(),
					'content_md5' => $sourcefh->getHash(),
					'updated' => $sourcefh->getMTime(),
				]
			);

			$original->save();
		}

		// All destination files get written to the custom directory!
		$customfh->putContents($newmodel->get('content'));
		$hash = $customfh->getHash();

		/*
		// What happens now is based on the type of the inbound file.
		switch($activefile){
			case 'skin':
				// Just replace the contents of that file.
				$fh->putContents($newmodel->get('content'));
				$hash = $fh->getHash();
				break;
			case 'template':
				// This gets written into the current theme directory.
				$themefh = new File_local_backend(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $file);
				$themefh->putContents($newmodel->get('content'));
				$hash = $themefh->getHash();
				break;
			case 'style':
			case 'file':
				// This gets written into the current theme directory.
				$themefh = new File_local_backend(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $file);
				$themefh->putContents($newmodel->get('content'));
				$hash = $themefh->getHash();

				// This is required to get assets updated to the CDN correctly.
				$theme = ThemeHandler::GetTheme();
				$hash = $themefh->getHash();
				$theme->addAssetFile(array('file' => $file, 'md5' => $hash));
				$theme->save();
				$theme->reinstall();
			default:
		}
*/

		// Make a record of this change too!
		$change = new ThemeTemplateChangeModel();
		$change->setFromArray(
			[
				'comment' => $newmodel->get('comment'),
				'filename' => $file,
				'content' => $newmodel->get('content'),
				'content_md5' => $hash
			]
		);

		$change->save();


		if($activefile == 'file'){
			// Reinstall all assets too!
			foreach(Core::GetComponents() as $component){
				$component->reinstall();
			}
			// And the current theme.
			ThemeHandler::GetTheme(ConfigHandler::Get('/theme/selected'))->reinstall();
		}

		Core::SetMessage('Updated file successfully', 'success');
		return '/theme';
	}
}
