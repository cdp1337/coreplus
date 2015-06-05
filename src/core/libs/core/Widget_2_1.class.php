<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
			if ($this->getWidgetInstanceModel()) {
				// easy way
				$this->_view->baseurl = $this->getWidgetInstanceModel()->get('baseurl');
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
		$template = \Core\Templates\Template::Factory($page->get('last_template'));
		$areas    = $template->getWidgetAreas();

		if(!sizeof($areas)){
			// Selected template does not have any widget areas defined, no need to display the option then!
			return true;
		}

		// Otherwise...
		$view->addControl('Page Widgets', '/admin/widgets?baseurl=' . $page->get('baseurl'), 'cog');
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