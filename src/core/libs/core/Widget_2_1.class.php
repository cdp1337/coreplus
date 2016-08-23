<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

class Widget_2_1 {

	/**
	 * The view that gets returned when pages are executed.
	 *
	 * @var View
	 */
	private $_view = null;

	/**
	 * The widget request object for this call.
	 *
	 * @var WidgetRequest
	 */
	private $_request = null;

	/**
	 * Set this to true if this widget is "simple".
	 *
	 * Simple widgets to not require additional controllers and use the settings for the admin configuration.
	 *
	 * @var bool
	 */
	public $is_simple = false;

	/**
	 * Array of the settings as the key and their default value as the value.
	 *
	 * This is primarily useful on simple widgets, where Core's built-in admin manages the configuration and creation.
	 *
	 * @var array
	 */
	public $settings = [];

	/**
	 * The WidgetInstance for this request.  Every widget MUST be instanced on some widgetarea.
	 *
	 * @var WidgetInstanceModel
	 */
	public $_instance = null;

	/**
	 * Widgets that are manually called do not have instances attached to them,
	 * so parameters are not retrievable via that.
	 *
	 * This instead houses parameters for manually-called widgets. (ie: {widget ...} in the template)
	 *
	 * @var null|array
	 */
	public $_params = null;

	/**
	 * If this widget was called from a widgetarea with an "installable" property,  that value
	 * will be transposed here.  It may be useful for determining a userid or something... maybe.
	 *
	 * @var null|string
	 */
	public $_installable = null;

	/**
	 * The controls for ths widget
	 *
	 * @var ViewControls
	 */
	public $controls;


	/**
	 * Get the view for this controller.
	 * Up to the extending Controller to use this object is it wishes.
	 *
	 * @return View
	 */
	public function getView() {
		if ($this->_view === null) {

			$this->_view              = new View();
			$this->_view->contenttype = View::CTYPE_HTML;
			$this->_view->mode        = View::MODE_WIDGET;
			if (($wi = $this->getWidgetInstanceModel())) {
				// easy way
				$this->_view->baseurl = $wi->get('baseurl');

				$pagedat    = $wi->splitParts();
				$cnameshort = (strpos($pagedat['controller'], 'Widget') == strlen($pagedat['controller']) - 6) ?
					substr($pagedat['controller'], 0, -6) :
					$pagedat['controller'];
				
				if($wi->get('display_template')){
					// Convert that to the filename of the template.
					// This must be in /widgets/[widget-info]/displaytemplate.tpl.
					$this->_view->templatename = strtolower('/widgets/' . $cnameshort . '/' . $pagedat['method'] . '/' . $wi->get('display_template'));
				}
				else{
					$this->_view->templatename = strtolower('/widgets/' . $cnameshort . '/' . $pagedat['method'] . '.tpl');
				}
			}
			else {
				// difficult way
				$back = debug_backtrace();
				if(isset($back[1]['class'])){
					$cls  = $back[1]['class'];
					if (strpos($cls, 'Widget') !== false) $cls = substr($cls, 0, -6);
					$mth                  = $back[1]['function'];
					$this->_view->baseurl = $cls . '/' . $mth;
				}
			}
		}

		return $this->_view;
	}

	/**
	 * Add a control into this widget
	 *
	 * Useful for embedding functions and administrative utilities inline without having to adjust the
	 * application template.
	 *
	 * @param string|array|Model $title  The title to set for this control
	 * @param string             $link   The link to set for this control
	 * @param string|array       $class  The class name or array of attributes to set on this control
	 *                            If this is an array, it should be an associative array for the advanced parameters
	 */
	public function addControl($title, $link = null, $class = 'edit') {

		if($title instanceof Model){
			// Allow a raw Model to be sent in as the control subject.
			// This is a shortcut for Controllers much like the {controls} smarty function has.
			$this->controls = ViewControls::DispatchModel($title);
			$this->controls->setProxyText('Widget Controls');
			return;
		}

		if($this->controls === null){
			$this->controls = new ViewControls();
		}
		
		$control = new ViewControl();

		// Completely associative-array based version!
		if(func_num_args() == 1 && is_array($title)){
			foreach($title as $k => $v){
				$control->set($k, $v);
			}
		}
		else{
			// Advanced method, allow for associative arrays.
			if(is_array($class)){
				foreach($class as $k => $v){
					$control->set($k, $v);
				}
			}
			// Default method; just a string for the class name.
			else{
				$control->class = $class;
			}

			$control->title = $title;
			$control->link = \Core\resolve_link($link);
		}

		$this->controls->setProxyForce(true);
		$this->controls->setProxyText('Widget Controls');
		$this->controls[] = $control;
	}

	/**
	 * Add an array of controls at once, useful in conjunction with the model->getControlLinks method.
	 *
	 * If a Model is provided as the subject, that is used as the subject and all system hooks apply thereof.
	 *
	 * @param array|Model $controls
	 */
	public function addControls($controls){
		if($controls instanceof Model){
			// Allow a raw Model to be sent in as the control subject.
			// This is a shortcut for Controllers much like the {controls} smarty function has.
			$this->controls = ViewControls::DispatchModel($controls);
			$this->controls->setProxyText('Widget Controls');
			return;
		}

		foreach($controls as $c){
			$this->addControl($c);
		}
	}

	public function getRequest(){
		if($this->_request === null){
			$this->_request = new WidgetRequest();
		}

		return $this->_request;
	}


	/**
	 * Get the widget instance model for this widget
	 *
	 * @since 2.4.2
	 * @return WidgetInstanceModel
	 */
	public function getWidgetInstanceModel() {
		return $this->_instance;
	}

	/**
	 * Get the actual widget model for this instance.
	 *
	 * @since 2.4.2
	 * @return WidgetModel|null
	 */
	public function getWidgetModel(){
		$wi = $this->getWidgetInstanceModel();
		return $wi ? $wi->getLink('Widget') : null;
	}

	/**
	 * Get the form data for the settings on this widget.
	 *
	 * Has no effect for non-simple widgets.
	 *
	 * @return array
	 */
	public function getFormSettings(){
		return [];
	}

	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		// Extend this method in your class and return the path you need.
		// Optional.
		return '';
	}


	/**
	 * Set the access string for this view and do the access checks against the
	 * currently logged in user.
	 *
	 * Will also set the access string on the PageModel, since it needs to be reflected in the database.
	 *
	 * @since 2012.01
	 * @version 2.1
	 *
	 * @param string $accessstring
	 *
	 * @return boolean True or false based on access for current user.
	 */
	protected function setAccess($accessstring) {
		// Update the model
		$this->getWidgetInstanceModel()->set('access', $accessstring);

		return (\Core\user()->checkAccess($accessstring));
	}

	protected function setTemplate($template) {
		$this->getView()->templatename = $template;
	}

	protected function getParameter($param) {
		if($this->_params !== null){
			$parameters = $this->_params;
		}
		else{
			$dat = $this->getWidgetInstanceModel()->splitParts();
			$parameters = $dat['parameters'];
		}

		return (isset($parameters[$param])) ? $parameters[$param] : null;
	}

	/**
	 * @since 2.4.2
	 * @param $key
	 * @return mixed
	 */
	protected function getSetting($key){
		return $this->getWidgetModel()->getSetting($key);
	}


	/**
	 * Return a valid Widget.
	 *
	 * This is used because new $pagedat['controller'](); cannot provide typecasting :p
	 *
	 * @param string $name
	 *
	 * @return Widget_2_1|null
	 */
	public static function Factory($name) {
		if(class_exists($name) && is_subclass_of($name, 'Widget_2_1')){
			return new $name();
		}
		else{
			return null;
		}
	}

	/**
	 * Hook into /core/page/rendering to add the control link for this page if necessary and the user has the appropriate permissions.
	 */
	public static function HookPageRender(){
		$viewer = \Core\user()->checkAccess('p:/core/widgets/manage');
		$manager = \Core\user()->checkAccess('p:/core/widgets/manage');

		if(!($viewer || $manager)){
			// User does not have access to view nor to edit widgets, simply return out of here.
			return true;
		}

		$request  = \Core\page_request();
		$view     = \Core\view();
		$page     = $request->getPageModel();
		$tmplName = $page->get('last_template') ? $page->get('last_template') : $view->templatename;
		
		if(!$tmplName){
			// This page has no templates, ergo no widget areas.
			return true;
		}
		
		$template = \Core\Templates\Template::Factory($tmplName);
		$areas    = $template->getWidgetAreas();

		if(!sizeof($areas)){
			// Selected template does not have any widget areas defined, no need to display the option then!
			return true;
		}

		// Otherwise...
		$view->addControl('Page Widgets', '/widget/admin?template=' . $tmplName, 'cubes');
		return true;
	}

}

class WidgetRequest{
	public $parameters = [];

	/**
	 * Get all parameters from the GET or inline variables.
	 *
	 * "Core" parameters are returned on a 0-based index, whereas named GET variables are returned with their respective name.
	 *
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Get a single parameter from the GET or inline variables.
	 *
	 * @param $key string|int The parameter to request
	 *
	 * @return null|string
	 */
	public function getParameter($key) {
		return (array_key_exists($key, $this->parameters)) ? $this->parameters[$key] : null;
	}
}