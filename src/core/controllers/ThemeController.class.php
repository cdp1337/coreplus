<?php

/**
 * The main theme administration controller.
 *
 * This is responsible for all administrative tasks associated with the theme.
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
		// Set to true if multisite is enabled AND the page is currently on a child site.
		$multisite = Core::IsLibraryAvailable('multisite') && MultiSiteHelper::IsEnabled() && MultiSiteHelper::GetCurrentSiteID() != 0;
		$configDefault  = ConfigHandler::GetConfig('/theme/default_template');
		$configSelected = ConfigHandler::GetConfig('/theme/selected');
		$configEmailDefault  = ConfigHandler::GetConfig('/theme/default_email_template');
		// Only allow changing the theme if it's on the root site OR both config options are overrideable.
		$themeSelectionEnabled = (!$multisite || ($configDefault->get('overrideable') && $configSelected->get('overrideable')));
		$emailSelectionEnabled = (!$multisite || $configEmailDefault->get('overrideable'));

		$configOptions = $current->getConfigs();
		if(!sizeof($configOptions)){
			$optionForm = null;
		}
		else{
			$optionForm = new \Core\Forms\Form();
			$optionForm->set('callsmethod', 'AdminController::_ConfigSubmit');
			foreach($configOptions as $c){
				/** @var $c ConfigModel */
				
				if($multisite){
					// Only pull the config options that are enabled for this specific site.
					if($c->get('overrideable')){
						$optionForm->addElement($c->getAsFormElement());
					}
				}
				else{
					// Sites that either
					// do NOT have multisite installed
					// nor have multisite enabled
					// or on the root site, get all options.
					$optionForm->addElement($c->getAsFormElement());
				}
			}
			
			if(sizeof($optionForm->getElements()) > 0){
				// There is at least one element in the option forms!
				$optionForm->addElement('submit', ['value' => 'Save Configurable Options']);
			}
			else{
				// Reset the form back to null so that the section doesn't display.
				$optionForm = null;
			}
		}

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
			/** @var Component_2_1 $source */

			$dir = $source->getAssetDir();
			if(!$dir) continue;

			$dirlen = strlen($dir);
			$name = $source->getName();

			$dh = \Core\Filestore\Factory::Directory($dir);
			$ls = $dh->ls(null, true);
			foreach($ls as $obj){
				// Skip directories.
				if(!$obj instanceof \Core\Filestore\File) continue;

				/** @var $obj \Core\Filestore\File */
				$file = 'assets/' . substr($obj->getFilename(), $dirlen);

				// Since this is a template, it may actually be in a different location than where the package maintainer put it.
				// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
				$newobj = \Core\Filestore\Factory::File($file);

				$assets[$file] = array(
					'file' => $file,
					'obj' => $newobj,
					'component' => $name,
					'haswidgets' => false,
					'has_stylesheets' => false,
					'type' => 'asset',
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



		// Get the templates throughout the site.  These can include pages, emails, form elements, etc.
		$components = Core::GetComponents();
		$templates = array();

		foreach($components as $c) {
			/** @var $c Component_2_1 */
			$dir = $c->getViewSearchDir();
			if(!$dir) continue;

			$dirlen = strlen($dir);
			$component = $c->getName();

			$dh = \Core\Filestore\Factory::Directory($dir);
			//$pagetplfiles = $dh->ls('tpl', true);
			$pagetplfiles = $dh->ls(null, true);

			// not sure why getFilename(path) isn't working as expected, but this works too.
			foreach($pagetplfiles as $obj){

				// I don't want directories.
				if($obj instanceof \Core\Filestore\Directory) continue;

				/** @var $obj \Core\Filestore\File */
				$file = substr($obj->getFilename(), $dirlen);

				// Since this is a template, it may actually be in a different location than where the package maintainer put it.
				// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
				$tpl = Core\Templates\Template::Factory($file);
				$resolved = Core\Templates\Template::ResolveFile($file);

				$newobj = \Core\Filestore\Factory::File($resolved);

				$templates[$file] = array(
					'file' => $file,
					'resolved' => $resolved,
					'obj' => $newobj,
					'haswidgets' => $tpl->hasWidgetAreas(),
					'component' => $component,
					'has_stylesheets' => $tpl->hasOptionalStylesheets(),
					'type' => 'template',
				);
			}
		}

		// Now that the template files have been loaded into a flat array, I need to convert that to the properly nested version.
		ksort($templates);
		$nestedtemplates = array();
		foreach($templates as $k => $obj){
			$parts = explode('/', $k);
			$lastkey = sizeof($parts) - 1;
			$thistarget =& $nestedtemplates;
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
		
		$siteskinform = new \Core\Forms\Form();
		$siteskinform->set('callsmethod', 'ThemeController::SaveSiteSkins');
		$opts = ['' => '-- Public Default --'];
		foreach($current->getSkins() as $skin){
			$opts[$skin['file']] = $skin['title'];
		}
		foreach(ConfigHandler::FindConfigs('/theme/siteskin/') as $k => $config){
			$siteskinform->addElement(
				'select',
				[
					'name' => 'config[' . $k . ']',
					'title' => $config->get('description'),
					'value' => $config->getValue(),
					'options' => $opts,
				]
			);
		}

		$siteskinform->addElement('submit', ['value' => t('STRING_SAVE')]);


		$customdest = \Core\directory('themes/custom');

		$cssform = false;
		$cssprintform = false;

		if($customdest->isWritable()){
			$sets = [
				[
					'title' => 't:STRING_SAVE_CUSTOM_CSS',
					'file' => 'css/custom.css',
				    'form' => null,
				],
				[
					'title' => 't:STRING_SAVE_CUSTOM_PRINT_CSS',
					'file' => 'css/custom_print.css',
					'form' => null,
				],
			];

			foreach($sets as $k => $set){
				// Load the editor for the custom CSS file, as editing site CSS is a very common thing to do!
				$file  = $set['file'];
				$title = $set['title'];
				
				// This file must ALWAYS be in themes/custom.
				$fh = \Core\Filestore\Factory::File(ROOT_PDIR . 'themes/custom/assets/' . $file);
				$content = $fh->getContents();

				$m = new ThemeTemplateChangeModel();
				$m->set('content', $content);
				$m->set('filename', 'assets/' . $file);

				$form = \Core\Forms\Form::BuildFromModel($m);

				$form->set('callsmethod', 'ThemeController::_SaveEditorHandler');
				// I need to add the file as a system element so core doesn't try to reuse the same forms on concurrent edits.
				//$form->addElement('system', array('name' => 'revision', 'value' => $revision));
				$form->addElement('system', array('name' => 'file', 'value' => 'assets/' . $file));
				$form->addElement('system', array('name' => 'filetype', 'value' => 'file'));
				// No one uses this anyways!
				$form->switchElementType('model[comment]', 'hidden');

				$form->getElement('model[content]')->set('id', 'custom_content_' . $k);

				$form->addElement('submit', array('value' => $title));

				// Save it back down to the original array
				$sets[$k]['form'] = $form;
			}
			
			$cssform = $sets[0]['form'];
			$cssprintform = $sets[1]['form'];
		}

		$view->title = 'Theme Manager';
		$view->assign('themes', $themes);
		$view->assign('current', $current);
		$view->assign('options_form', $optionForm);
		$view->assign('assets', $nestedassets);
		$view->assign('templates', $nestedtemplates);
		$view->assign('url_themeeditor', \Core\resolve_link('/theme/editor'));
		$view->assign('url_themewidgets', \Core\resolve_link('/theme/widgets'));
		$view->assign('url_themestylesheets', \Core\resolve_link('/theme/selectstylesheets'));
		$view->assign('site_skins_form', $siteskinform);
		$view->assign('cssform', $cssform);
		$view->assign('cssprintform', $cssprintform);
		$view->assign('multisite', $multisite);
		$view->assign('theme_selection_enabled', $themeSelectionEnabled);
		$view->assign('email_selection_enabled', $emailSelectionEnabled);
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

			$dh = \Core\Filestore\Factory::Directory($dir);
			$pagetplfiles = $dh->ls('tpl', true);

			// not sure why getFilename(path) isn't working as expected, but this works too.
			foreach($pagetplfiles as $obj){
				/** @var $obj \Core\Filestore\File */
				$file = substr($obj->getFilename(), $dirlen);

				// Since this is a template, it may actually be in a different location than where the package maintainer put it.
				// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
				$resolved = Core\Templates\Template::ResolveFile($file);
				$newobj = \Core\Filestore\Factory::File($resolved);

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
		$dh = \Core\Filestore\Factory::Directory($dir);
		$cssls = $dh->ls('css', true);
		foreach($cssls as $obj){
			/** @var $obj \Core\Filestore\Backends\FileLocal */
			$file = 'assets' . substr($obj->getFilename(), $dirlen);

			// Since this is a template, it may actually be in a different location than where the package maintainer put it.
			// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
			$newobj = \Core\Filestore\Factory::File($file);

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

			$dh = \Core\Filestore\Factory::Directory($dir);
			$cssls = $dh->ls('css', true);

			// not sure why getFilename(path) isn't working as expected, but this works too.
			foreach($cssls as $obj){
				/** @var $obj \Core\Filestore\Backends\FileLocal */
				$file = 'assets' . substr($obj->getFilename(), $dirlen);

				// Since this is a template, it may actually be in a different location than where the package maintainer put it.
				// ie: user template user/templates/pages/user/view.tpl may be installed to themes/myawesometheme/pages/user/view.tpl instead.
				$newobj = \Core\Filestore\Factory::File($file);

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
		if(!\Core\Theme\Theme\ValidateThemeName($themename)){
			\Core\set_message('Invalid theme requested', 'error');
			\Core\go_back();
		}

		$theme = ThemeHandler::GetTheme($themename);


		if($template){
			// The template itself can be ignored.
			if(!\Core\Theme\Theme\ValidateTemplateName($themename, $template)){
				\Core\set_message('Invalid template requested', 'error');
				\Core\go_back();
			}
		}
		else{
			// and the default one is used otherwise.
			$allskins = $theme->getSkins();
			$template = $allskins[0]['file'];
		}


		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::GetCurrentSiteID()){
			$config_default  = ConfigHandler::GetConfig('/theme/default_template');
			$config_selected = ConfigHandler::GetConfig('/theme/selected');

			if($config_default->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				\Core\set_message('Unable to set the default template on a child site, please ensure that the "/theme/default_template" config is set to be overrideable!', 'error');
				\Core\go_back();
			}
			if($config_selected->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				\Core\set_message('Unable to set the selected theme on a child site, please ensure that the "/theme/selected" config is set to be overrideable!', 'error');
				\Core\go_back();
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

			// reinstall theme and zee assets
			$t = ThemeHandler::GetTheme();

			if (($change = $t->reinstall(0)) !== false) {
				SystemLogModel::LogInfoEvent('/updater/theme/reinstall', 'Theme ' . $t->getName() . ' reinstalled successfully', implode("\n", $change));
			}

			\Core\set_message('Updated default theme', 'success');
			\Core\redirect('/theme');
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
		if(!\Core\Theme\Theme\ValidateThemeName($theme)){
			\Core\set_message('Invalid theme requested', 'error');
			\Core\go_back();
		}

		if(!\Core\Theme\Theme\ValidateTemplateName($theme, $template)){
			\Core\set_message('Invalid template requested', 'error');
			\Core\go_back();
		}

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::GetCurrentSiteID()){
			$config_default  = ConfigHandler::GetConfig('/theme/default_admin_template');

			if($config_default->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				\Core\set_message('Unable to set the default template on a child site, please ensure that the "/theme/default_template" config is set to be overrideable!', 'error');
				\Core\go_back();
			}
		}

		if($request->isPost()){

			if($theme != ConfigHandler::Get('/theme/selected')){
				\Core\set_message('The admin skin must be on the same theme as the site!', 'error');
				\Core\go_back();
			}
			ConfigHandler::Set('/theme/default_admin_template', $template);

			\Core\set_message('Updated admin skin', 'success');
			\Core\go_back();
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
		if(!\Core\Theme\Theme\ValidateThemeName($theme)){
			\Core\set_message('Invalid theme requested', 'error');
			\Core\go_back();
		}

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::GetCurrentSiteID()){
			$config_default  = ConfigHandler::GetConfig('/theme/default_email_template');

			if($config_default->get('overrideable') == 0){
				// It's a child site and the admin never gave them permission to change default themes!
				\Core\set_message('Unable to set the default template on a child site, please ensure that the "/theme/default_email_template" config is set to be overrideable!', 'error');
				\Core\go_back();
			}
		}

		if($request->isPost()){

			if($theme != ConfigHandler::Get('/theme/selected')){
				\Core\set_message('The admin skin must be on the same theme as the site!', 'error');
				\Core\go_back();
			}
			ConfigHandler::Set('/theme/default_email_template', $template);

			\Core\set_message('Updated email skin', 'success');
			\Core\go_back();
		}
		else{
			return View::ERROR_BADREQUEST;
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
			\Core\set_message('No file requested', 'error');
			\core\redirect('/theme');
		}


		$fh = \Core\Filestore\Factory::File($filename);
		$customdest = \Core\directory('themes/custom');
		if(!$customdest->isWritable()){
			\Core\set_message('Directory themes/custom is not writable!  Inline file editing disabled.', 'error');
			\Core\go_back();
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
			\Core\set_message('Sorry, but only text files can be edited right now... Expect this to function soon though ;)', 'info');
			\Core\go_back();
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
				\Core\set_message('Invalid revision requested!', 'error');
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

		$form = \Core\Forms\Form::BuildFromModel($m);
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

		$form = new \Core\Forms\Form();
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

			\Core\set_message('Updated optional stylesheets successfully', 'success');
			\Core\go_back(1);
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
			\Core\set_message($message, 'error');
			\core\redirect('/theme');
		}
	}

	public static function _SaveEditorHandler(\Core\Forms\Form $form){
		$newmodel = $form->getModel();
		$file = $form->getElementValue('file');
		$activefile = $form->getElementValue('filetype');
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
				\Core\set_message('Unsupported file type: ' . $activefile, 'error');
				return false;
		}

		$customfh = \Core\Filestore\Factory::File($customfilename);
		if($customfh->exists()){
			// If the custom one exists... this will be the source file too!
			$sourcefh = $customfh;
		}
		else{
			$sourcefh = \Core\Filestore\Factory::File($filename);
		}


		// Check and see if they're the same, ie: no change.  I don't want to create a bunch of moot revisions.
		if($newmodel->get('content') == $sourcefh->getContents()){
			\Core\set_message('No changes performed.', 'info');
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
				$themefh = \Core\Filestore\Factory::File(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $file);
				$themefh->putContents($newmodel->get('content'));
				$hash = $themefh->getHash();
				break;
			case 'style':
			case 'file':
				// This gets written into the current theme directory.
				$themefh = \Core\Filestore\Factory::File(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $file);
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

		\Core\set_message('Updated file successfully', 'success');
		return '/theme';
	}

	public static function SaveSiteSkins(\Core\Forms\Form $form){
		foreach($form->getElements() as $el){
			/** @var FormElement $el */

			$n = $el->get('name');
			$v = $el->get('value');
			if(strpos($n, 'config[') === 0){
				$k = substr($n, 7, -1);
				ConfigHandler::Set($k, $v);
			}
		}

		return true;
	}
}
