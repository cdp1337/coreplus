<?php

/**
 * Widget controller, management interface for widgets and the like.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

class WidgetController extends Controller_2_1 {

	public function __construct() {

	}

	/**
	 * Display a listing of all widgets registered in the system.
	 */
	public function admin(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$viewer = \Core\user()->checkAccess('p:/core/widgets/manage');
		$manager = \Core\user()->checkAccess('p:/core/widgets/manage');
		if(!($viewer || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		// Build a list of create pages for all registered components.
		$components    = Core::GetComponents();
		$pages         = [];
		$skins         = [];
		$selected      = null;
		$selectedtype  = null;
		$baseurl       = null;
		$selectoptions = [];
		$links         = [];
		$theme         = ThemeHandler::GetTheme();
		$formtheme     = null;
		$formskin      = null;
		$formtemplate  = null;

		foreach($components as $c){
			/** @var Component_2_1 $c */

			$viewdir = $c->getViewSearchDir();
			if($viewdir){
				$dirlen = strlen($viewdir);
				$component = $c->getName();

				$dh = \Core\Filestore\Factory::Directory($viewdir);
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

					if($tpl->hasWidgetAreas()){
						$pagetitle = $file;
						if(strpos($pagetitle, 'pages/') === 0){
							$pagetitle = substr($pagetitle, 6);
						}
						// Replace directory slashes with a space
						$pagetitle = str_replace(['/', '-'], ' ', $pagetitle);
						// Capitalize them
						$pagetitle = ucwords($pagetitle);
						// And trim off the ".tpl" suffix.
						$pagetitle = substr($pagetitle, 0, -4);
						$pages[$file] = $pagetitle;
					}
				}
			}
			
			$links = array_merge($links, $c->getWidgetCreatesDefined());
		}

		// Build the array of skins for the current theme
		$themeskins  = $theme->getSkins();
		$defaultskin = null;
		foreach($themeskins as $dat){

			$skins[ 'skins/' . $dat['file'] ] = $dat['title'];

			if($dat['default']){
				$defaultskin = 'skins/' . $dat['file'];
			}
		}

		// Now that the various templates have been loaded into a flat array, I need to sort them.
		asort($pages);
		asort($skins);

		foreach($skins as $k => $v){
			$selectoptions[ $k ] = 'Skin: ' . $v;
		}
		foreach($pages as $k => $v){
			$selectoptions[ $k ] = 'Page: ' . $v;
		}

		if($request->getParameter('baseurl')){
			// It's a URL-specific request, lookup which template that page used last.
			$baseurl  = $request->getParameter('baseurl');
			$page     = PageModel::Construct($baseurl);

			if(!isset($pages[ $page->get('last_template') ])){
				\Core\set_message('Requested page template does not seem to contain any widget areas.', 'error');
				\Core\go_back();
			}

			$selected = $page->get('last_template');
			$selectedtype = 'url';
			$formtemplate = $selected;
		}
		elseif($request->getParameter('template')){
			$selected = $request->getParameter('template');
			
			// Ensure that it does not start with a '/'.
			// This is because the query is without one.
			if($selected{0} == '/'){
				$selected = substr($selected, 1);
			}

			if(isset($pages[ $selected ])){
				$selectedtype = 'page';
				$formtemplate = $selected;
			}
			else{
				$selectedtype = 'skin';
				$formtheme = $theme->getKeyName();
				$formskin  = $selected;
			}
		}
		else{
			// Just use the default theme skin.
			$selected = $defaultskin;
			$selectedtype = 'skin';$formtheme = $theme->getKeyName();
			$formskin  = $selected;
		}

		$template     = \Core\Templates\Template::Factory($selected);
		$areas        = $template->getWidgetAreas();
		$installables = [0 => ''];

		foreach($areas as $k => $dat){
			// Ensure that each area has a widgets array, (even if it's empty)
			$areas[$k]['widgets'] = [];
			$installables[] = $dat['installable'];
		}
		$installables = array_unique($installables);

		$factory = new ModelFactory('WidgetInstanceModel');
		$factory->order('weight');
		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			$factory->whereGroup('or', ['site = -1', 'site = ' . MultiSiteHelper::GetCurrentSiteID()]);
		}

		if($selectedtype == 'skin'){
			// First, the skin-level where clause.
			$skinwhere = new Core\Datamodel\DatasetWhereClause();
			$skinwhere->setSeparator('AND');
			//$skinwhere->addWhere('theme = ' . $theme->getKeyName());
			$skinwhere->addWhere('template = ' . $selected);
			$factory->where($skinwhere);
		}
		elseif($selectedtype == 'page'){
			$factory->where('template = ' . $selected);
		}
		elseif($selectedtype == 'url'){
			$factory->where('page_baseurl = ' . $baseurl);
		}
		else{
			\Core\set_message('Invalid/unknown template type', 'error');
			\Core\go_back();
		}


		foreach($factory->get() as $wi){
			/** @var $wi WidgetInstanceModel */

			$a = $wi->get('widgetarea');
			$areas[$a]['widgets'][] = $wi;
		}

		$available = WidgetModel::Find(['installable IN ' . implode(', ', $installables)]);
		
		/*
		$table = new Core\ListingTable\Table();
		$table->setName('/admin/widgets');
		$table->setModelName('WidgetModel');
		// Add in all the columns for this listing table.
		$table->addColumn('Title', 'title');
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled() && \Core\user()->checkAccess('g:admin')){
			$table->addColumn('Site', 'site', false);
			$ms = true;
		}
		else{
			$ms = false;
		}
		$table->getModelFactory()->where('installable IN ' . implode(', ', $installables));
		$table->addColumn('Base URL', 'baseurl');
		$table->addColumn('Installable', 'installable');
		$table->addColumn('Created', 'created');

		$table->loadFiltersFromRequest();
		*/

		$view->mastertemplate = 'admin';
		$view->title = 'All Widgets';
		//$view->assign('table', $table);
		$view->assign('available_widgets', $available);
		$view->assign('links', $links);
		$view->assign('manager', $manager);
		$view->assign('theme', $formtheme);
		$view->assign('skin', $formskin);
		$view->assign('template', $selected);
		$view->assign('page_template', $formtemplate);
		$view->assign('page_baseurl', $baseurl);
		$view->assign('options', $selectoptions);
		$view->assign('selected', $selected);
		$view->assign('areas', $areas);
		//$view->assign('multisite', $ms);
	}

	/**
	 * Create a simple widget with the standard settings configurations.
	 */
	public function create(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		// Only widget managers can create widgets.
		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$class = $request->getParameter('class');

		// If it doesn't end in "widget", it should!
		if(stripos($class, 'widget') != strlen($class) - 6){
			$class .= 'Widget';
		}

		if(!class_exists($class)){
			\Core\set_message('t:MESSAGE_ERROR_CORE_WIDGET_S_NOT_FOUND_ON_SYSTEM', $class);
			\Core\go_back();
		}

		/** @var \Core\Widget $obj */
		$obj = new $class();

		if(!($obj instanceof \Core\Widget)){
			\Core\set_message('t:MESSAGE_ERROR_CORE_WIDGET_S_WRONG_PARENT_CLASS', $class);
			\Core\go_back();
		}

		if(!$obj->is_simple){
			\Core\set_message('t:MESSAGE_ERROR_CORE_WIDGET_S_NOT_SIMPLE_WIDGET', $class);
			\Core\go_back();
		}

		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'WidgetController::_CreateUpdateHandler');

		// Make the widget's "baseurl", which for simple widgets will be the widget class followed by a UUID.
		$baseurl = '/' . strtolower(substr($class, 0, -6)) . '/execute/';
		$baseurl .= Core::GenerateUUID();

		$form->addElement('system', ['name' => 'baseurl', 'value' => $baseurl]);
		
		$form->addElement(
			'text',
			[
				'name' => 'title',
				'required' => true,
				'title' => t('STRING_CORE_WIDGET_ADMIN_TITLE'),
				'description' => t('MESSAGE_CORE_WIDGET_ADMIN_TITLE')
			]
		);

		$defaults = $obj->settings;
		$formdata = $obj->getFormSettings();

		foreach($formdata as $dat){
			$type = $dat['type'];
			$name = $dat['name'];

			$dat['name'] = 'setting[' . $name . ']';

			$form->addElement($type, $dat);
		}
		
		// Managers can manage the edit permissions for this particular widget.
		// This allows managers to delegate edit permissions for particular areas on the site.
		$form->addElement(
			'access',
			[
				'name' => 'editpermissions',
				'title' => t('STRING_CORE_WIDGET_EDIT_PERMISSIONS'),
				'description' => t('MESSAGE_CORE_WIDGET_EDIT_PERMISSIONS'),
				'value' => '!*',
			]
		);


		$form->addElement('submit', ['value' => t('STRING_CORE_WIDGET_CREATE')]);

		$view->mastertemplate = 'admin';
		$view->addBreadcrumb('All Widgets', '/widget/admin');
		$view->title = t('STRING_CORE_WIDGET_CREATE');
		$view->assign('form', $form);
	}

	/**
	 * Create a simple widget with the standard settings configurations.
	 */
	public function update(){
		$view = $this->getView();
		$request = $this->getPageRequest();
		
		$manager = \Core\user()->checkAccess('p:/core/widgets/manage');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$baseurl = $request->getParameter('baseurl');
		$class = substr($baseurl, 1, strpos($baseurl, '/', 1)-1) . 'widget';

		if(!class_exists($class)){
			\Core\set_message('t:MESSAGE_ERROR_CORE_WIDGET_S_NOT_FOUND_ON_SYSTEM', $class);
			\Core\go_back();
		}

		/** @var \Core\Widget $obj */
		$obj = new $class();

		if(!($obj instanceof \Core\Widget)){
			\Core\set_message('t:MESSAGE_ERROR_CORE_WIDGET_S_WRONG_PARENT_CLASS', $class);
			\Core\go_back();
		}

		if(!$obj->is_simple){
			\Core\set_message('t:MESSAGE_ERROR_CORE_WIDGET_S_NOT_SIMPLE_WIDGET', $class);
			\Core\go_back();
		}

		$model = new WidgetModel($baseurl);
		
		if(!$model->exists()){
			return View::ERROR_NOTFOUND;
		}
		
		// Check that this user has permission to edit if they are not a manager.
		if(!$manager && !\Core\user()->checkAccess($model->get('editpermissions'))){
			return View::ERROR_ACCESSDENIED;
		}

		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'WidgetController::_CreateUpdateHandler');

		$form->addElement('system', ['name' => 'baseurl', 'value' => $baseurl]);

		if($manager){
			$form->addElement(
			'text',
				[
					'name' => 'title',
					'required' => true,
					'value' => $model->get('title'),
					'title' => t('STRING_CORE_WIDGET_ADMIN_TITLE'),
					'description' => t('MESSAGE_CORE_WIDGET_ADMIN_TITLE')
				]
			);
		}
		

		$defaults = $obj->settings;
		$formdata = $obj->getFormSettings();

		foreach($formdata as $dat){
			$type = $dat['type'];
			$name = $dat['name'];

			$dat['value'] = $model->getSetting($name) !== null ? $model->getSetting($name) : $defaults[$name];
			$dat['name'] = 'setting[' . $name . ']';

			$form->addElement($type, $dat);
		}
		
		if($manager){
			// Managers can manage the edit permissions for this particular widget.
			// This allows managers to delegate edit permissions for particular areas on the site.
			$form->addElement(
				'access',
				[
					'name' => 'editpermissions',
					'title' => t('STRING_CORE_WIDGET_EDIT_PERMISSIONS'),
					'description' => t('MESSAGE_CORE_WIDGET_EDIT_PERMISSIONS'),
					'value' => $model->get('editpermissions'),
				]
			);
		}


		$form->addElement('submit', ['value' => t('STRING_CORE_WIDGET_UPDATE')]);

		$view->mastertemplate = 'admin';
		$view->addBreadcrumb('All Widgets', '/widget/admin');
		$view->title = t('STRING_CORE_WIDGET_UPDATE');
		$view->assign('form', $form);
	}

	/**
	 * Delete a simple widget.
	 */
	public function delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$baseurl = $request->getParameter('baseurl');
		$baseurl = $request->getParameter('baseurl');
		if($baseurl{0} == '/'){
			// Trim off the beginning '/' from the URL.
			$class = substr($baseurl, 1, strpos($baseurl, '/', 1)-1) . 'widget';
		}
		else{
			$class = substr($baseurl, 0, strpos($baseurl, '/')) . 'widget';
		}
		
		if(!class_exists($class)){
			\Core\set_message('t:MESSAGE_ERROR_CORE_WIDGET_S_NOT_FOUND_ON_SYSTEM', $class);
			\Core\go_back();
		}

		/** @var \Core\Widget $obj */
		$obj = new $class();

		if(!($obj instanceof \Core\Widget)){
			\Core\set_message('Wrong parent class for [' . $class . '], it does not appear to be a Widget instance, invalid widget!', 'error');
			\Core\go_back();
		}

		if(!$obj->is_simple){
			\Core\set_message('Widget [' . $class . '] does not appear to be a simple widget.  Only simple widgets can be created via this page.', 'error');
			\Core\go_back();
		}

		$model = new WidgetModel($baseurl);

		$model->delete();
		\Core\set_message('Deleted widget ' . $model->get('title') . ' successfully!', 'success');
		\Core\go_back();
	}

	public function instances_save(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$counter = 0;
		$changes = ['created' => 0, 'updated' => 0, 'deleted' => 0];

		$selected = $_POST['selected'];
		// For the incoming options, I want an explicit NULL if it's empty.
		$theme    = $_POST['theme'] == '' ? null : $_POST['theme'];
		$skin     = $_POST['skin'] == '' ? null : $_POST['skin']; $_POST['skin'];
		$template = $_POST['template'] == '' ? null : $_POST['template'];
		$baseurl  = $_POST['page_baseurl'] == '' ? null : $_POST['page_baseurl'];

		foreach($_POST['widgetarea'] as $id => $dat){

			// Merge in the global information for this request
			//$dat['theme']         = $theme;
			//$dat['skin']          = $skin;
			$dat['template']      = $template;
			$dat['page_baseurl']  = $baseurl;

			$dat['weight'] = ++$counter;
			$dat['access'] = $dat['widgetaccess'];

			$w = WidgetModel::Construct($dat['baseurl']);
			$dat['site'] = $w->get('site');

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

			\Core\set_message(implode('<br/>', $changetext), 'success');
		}
		else{
			\Core\set_message('t:MESSAGE_INFO_NO_CHANGES_PERFORMED');
		}

		if($baseurl){
			\Core\redirect($baseurl);
		}
		else{
			\Core\redirect('/widget/admin?selected=' . $selected);
		}
	}

	/**
	 * Controller view to install 1 widget into one selected area, be that area a skin, or page template.
	 *
	 * @return int
	 */
	public function instance_install(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		// For the incoming options, I want an explicit NULL if it's empty.
		$template       = (isset($_POST['template']) && $_POST['template'] != '') ? $_POST['template'] : null;
		$page_baseurl   = (isset($_POST['page_baseurl']) && $_POST['page_baseurl'] != '') ? $_POST['page_baseurl'] : null;
		$widget_baseurl = (isset($_POST['widget_baseurl']) && $_POST['widget_baseurl'] != '') ? $_POST['widget_baseurl'] : null;
		$widgetarea     = (isset($_POST['area']) && $_POST['area'] != '') ? $_POST['area'] : null;

		$wm = WidgetModel::Construct($widget_baseurl);
		$dat['site']         = $wm->get('site');
		$dat['baseurl']      = $widget_baseurl;
		$dat['template']     = $template;
		$dat['page_baseurl'] = $page_baseurl;
		$dat['widgetarea']   = $widgetarea;

		if($template){
			$counter = WidgetInstanceModel::Count(['template = ' . $template, 'widgetarea = ' . $widgetarea]);
			$w = new WidgetInstanceModel();
			$w->setFromArray($dat);
			$w->set('weight', $counter + 1);
			$w->save();

			\Core\set_message('Installed widget into requested template', 'success');
		}
		elseif($page_baseurl){
			$counter = WidgetInstanceModel::Count(['page_baseurl = ' . $page_baseurl, 'widgetarea = ' . $widgetarea]);
			$w = new WidgetInstanceModel();
			$w->setFromArray($dat);
			$w->set('weight', $counter + 1);
			$w->save();

			\Core\set_message('Installed widget into requested page', 'success');
		}
		else{
			\Core\set_message('Unknown request', 'error');
		}

		if($page_baseurl){
			\Core\redirect($page_baseurl);
		}
		else{
			\Core\redirect('/widget/admin?template=' . $template);
		}
	}

	/**
	 * Controller view to update any instance-specific options for a given template.
	 *
	 * Usually consists of just access permissions and display template, but more options could come in the future.
	 */
	public function instance_update(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$instance = WidgetInstanceModel::Construct($request->getParameter(0));
		if(!$instance->exists()){
			return View::ERROR_NOTFOUND;
		}
		
		$widget = $instance->getWidget();

		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'WidgetController::_InstanceHandler');

		$form->addModel($instance);
		
		// Add any of the widget display settings that may be set in the Widget.
		foreach($widget->displaySettings as $idx => $setting){
			// Pull the type of the form element from the array, or text as the default.
			$type = isset($setting['type']) ? $setting['type'] : 'text';
			$name = isset($setting['name']) ? $setting['name'] : $idx;
			
			// Remap the name to a name that the form handler will know about.
			$setting['name'] = 'display_setting[' . $name . ']';
			
			if($type == 'checkbox'){
				// This one requires some additional work;
				// The value is true or false, but the attribute to set is 'checked'.
				if($setting['value'] === 1 || $setting['value'] === '1' || $setting['value'] === true){
					$setting['checked'] = true;
				}
				else{
					$setting['checked'] = false;
				}
				
				$setting['value'] = '1';
			}
			
			$form->addElement($type, $setting);
		}
		
		$form->addElement('submit', ['value' => 'Save Options']);

		$view->mastertemplate = 'admin';
		$view->addBreadcrumb('All Widgets', '/widget/admin');
		$view->title = 'Update Installed Options';
		$view->assign('form', $form);
	}

	/**
	 * Controller view to update any instance-specific options for a given template.
	 *
	 * Usually consists of just access permissions and display template, but more options could come in the future.
	 */
	public function instance_remove(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$instance = WidgetInstanceModel::Construct($request->getParameter(0));
		if(!$instance->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$otherCriteria = [
			'site = ' . $instance->get('site'),
			'template = ' . ($instance->get('template') === null ? 'NULL' : $instance->get('template')),
			'page_baseurl = ' . ($instance->get('page_baseurl') === null ? 'NULL' : $instance->get('page_baseurl')),
			'widgetarea = ' . $instance->get('widgetarea'),
		];

		$instance->delete();

		// Reshuffle the rest of the widgets to ensure that they are ordered correctly.
		$other = WidgetInstanceModel::Find($otherCriteria, null, 'weight');
		$x = 0;
		foreach($other as $o){
			/** @var WidgetInstanceModel $o */
			$o->set('weight', ++$x);
			$o->save();
		}

		\Core\go_back();
	}

	/**
	 * Controller view to update any instance-specific options for a given template.
	 *
	 * Usually consists of just access permissions and display template, but more options could come in the future.
	 */
	public function instance_moveup(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$instance = WidgetInstanceModel::Construct($request->getParameter(0));
		if(!$instance->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		// Figure out which instance is this one -1.
		$otherCriteria = [
			'site = ' . $instance->get('site'),
			'template = ' . ($instance->get('template') === null ? 'NULL' : $instance->get('template')),
			'page_baseurl = ' . ($instance->get('page_baseurl') === null ? 'NULL' : $instance->get('page_baseurl')),
			'widgetarea = ' . $instance->get('widgetarea'),
			'weight = ' . ($instance->get('weight') - 1),
		];
		$other = WidgetInstanceModel::Find($otherCriteria, 1);

		if(!$other){
			\Core\set_message('Widget is already in the top position!', 'error');
		}
		else{
			$other->set('weight', $other->get('weight') + 1);
			$instance->set('weight', $instance->get('weight') - 1);

			$other->save();
			$instance->save();
		}

		\Core\go_back();
	}

	/**
	 * Controller view to update any instance-specific options for a given template.
	 *
	 * Usually consists of just access permissions and display template, but more options could come in the future.
	 */
	public function instance_movedown(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/core/widgets/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$instance = WidgetInstanceModel::Construct($request->getParameter(0));
		if(!$instance->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		// Figure out which instance is this one -1.
		$otherCriteria = [
			'site = ' . $instance->get('site'),
			'template = ' . ($instance->get('template') === null ? 'NULL' : $instance->get('template')),
			'page_baseurl = ' . ($instance->get('page_baseurl') === null ? 'NULL' : $instance->get('page_baseurl')),
			'widgetarea = ' . $instance->get('widgetarea'),
			'weight = ' . ($instance->get('weight') + 1),
		];
		$other = WidgetInstanceModel::Find($otherCriteria, 1);

		if(!$other){
			\Core\set_message('Widget is already in the bottom position!', 'error');
		}
		else{
			$other->set('weight', $other->get('weight') - 1);
			$instance->set('weight', $instance->get('weight') + 1);

			$other->save();
			$instance->save();
		}

		\Core\go_back();
	}


	public static function _CreateUpdateHandler(\Core\Forms\Form $form){
		$baseurl = $form->getElement('baseurl')->get('value');

		$model = new WidgetModel($baseurl);
		$model->set('editurl', '/widget/update?baseurl=' . $baseurl);
		$model->set('deleteurl', '/widget/delete?baseurl=' . $baseurl);
		
		if(\Core\user()->checkAccess('p:/core/widgets/manage')){
			// Managers have additional permissions to edit advanced fields.
			$model->set('title', $form->getElementValue('title'));
			$model->set('editpermissions', $form->getElementValue('editpermissions'));
		}
		

		$elements = $form->getElements();
		foreach($elements as $el){
			/** @var FormElement $el */
			if(strpos($el->get('name'), 'setting[') === 0){
				$name = substr($el->get('name'), 8, -1);
				$model->setSetting($name, $el->get('value'));
			}
		}
		$model->save();
		
		\Core\set_message('t:MESSAGE_SUCCESS_CORE_WIDGET_S_UPDATED', $model->get('title'));

		return 'back';
	}

	public static function _InstanceHandler(\Core\Forms\Form $form){
		$instance = $form->getModel();
	
		// Set any display settings that may be present.
		$displaySettings = $form->getElementsByName('display_setting\[.*\]');
		$displaySettingValues = [];
		foreach($displaySettings as $el){
			
			$name = substr($el->get('name'), 16, -1);
			$val  = $el->get('value');
			$displaySettingValues[$name] = $val;
		}
		$instance->set('display_settings', $displaySettingValues);
		
		$instance->save();

		return 'back';
	}
}
