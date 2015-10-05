<?php
/**
 * Defines the schema for the WidgetModel table
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

class WidgetModel extends Model {

	/**
	 * A cache of the settings available from the model.
	 * This saves from having to perform the jsondecode every time a setting is requested.
	 *
	 * @var array
	 */
	private $_settings = null;

	/** @var Widget_2_1|null */
	private $_widget;


	public static $Schema = array(
		'site' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => -1,
			'formtype' => 'system',
			'comment' => 'The site id in multisite mode, (or -1 if global)',
		),
		'baseurl' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required'  => true,
			'null'      => false,
			'link'      => [
				'model' => 'WidgetInstance',
				'type'  => Model::LINK_HASMANY,
				'on'    => 'baseurl',
			],
		),
		// This indicates which type of widgetarea it's installable to.
		// ie: {widgetarea installable="/admin"} and {widgetarea installable="/user-social/view/###"}
		// Anything installable in / is acceptable in any of them,
		// null is a regular one not applicable to page level templates.
		// A public user widget for ie: Recent Blog Posts
		'installable' => array(
			'type'    => Model::ATT_TYPE_STRING,
			'null'    => false,
			'default' => '',
			'comment' => 'Baseurl that this widget "plugs" into, if any.',
		),
		'title'   => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default'   => null,
			'comment'   => '[Cached] Title of the page',
			'null'      => true,
		),
		'settings' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'formtype' => 'disabled',
			'comment' => 'Provides a section for saving json-encoded settings on the widget.'
		),
		'editurl' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default'   => '',
			'required'  => false,
			'null'      => false,
			'comment'   => 'The URL to edit this widget',
		),
		'deleteurl' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default'   => '',
			'required'  => false,
			'null'      => false,
			'comment'   => 'The URL to perform the POST on to delete this widget',
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('baseurl'),
	);


	/**
	 * Get a setting from the json-encoded settings string.
	 *
	 * This value is decoded and is the same as the data that went in.
	 *
	 * @param $key
	 * @return mixed
	 */
	public function getSetting($key){
		if($this->_settings === null){
			// settings is a json encoded array.
			$string = $this->get('settings');
			if($string){
				$this->_settings = json_decode($this->get('settings'), true);
				if(!$this->_settings) $this->_settings = array();
			}
			else{
				$this->_settings = array();
			}
		}

		return (isset($this->_settings[$key])) ? $this->_settings[$key] : null;
	}


	/**
	 * Set a given setting that is to be saved into the json encoded string.
	 *
	 * @param $key
	 * @param $value
	 */
	public function setSetting($key, $value){
		// First I need to get the settings.  This will do 2 things,
		// allow me to load up the json encoded string (and not have to duplicate that code),
		// and allow the values to be checked so if the value is unchanged, no action needs be performed.

		$current = $this->getSetting($key);

		// Is it the same?
		if($current === $value) return;

		// Nope?  Ok, save in the cache and the json encoded string.
		$this->_settings[$key] = $value;

		$this->set('settings', json_encode($this->_settings));
	}


	/**
	 * Widgets are linked via their baseurl, but if the baseurl has an id, ie:
	 * /something/view/123, then 123 will be returned here.
	 *
	 * This is more of a case-by-case method, as not all widgets will have an id.
	 */
	public function getID(){
		// The easiest and most reliable way to parse widget urls is via the builtin Split function :p
		$split = self::SplitBaseURL($this->get('baseurl'));

		// If it's null, then it's not a valid url... ok
		if(!$split) return null;

		// Otherwise, if there's a parameter 0, that is usually the id.
		// and if not, then WHY ARE YOU CALLING THIS METHOD?
		if(isset($split['parameters'][0])) return $split['parameters'][0];
		else return null;
	}

	/**
	 * Get an array of all the parts of this request, including:
	 * 'controller', 'method', 'parameters', 'baseurl', 'rewriteurl'
	 *
	 * @return array
	 */
	public function splitParts() {
		$ret = WidgetModel::SplitBaseURL($this->get('baseurl'));

		// No?
		if (!$ret) {
			$ret = [
				'controller' => null,
				'method'     => null,
				'parameters' => null,
				'baseurl'    => null
			];
		}

		// Tack on the parameters
		if ($ret['parameters'] === null) $ret['parameters'] = [];

		return $ret;
	}

	/**
	 * Get the associated model for this instance, if available.
	 *
	 * @return Widget_2_1|null
	 */
	public function getWidget(){
		if($this->_widget === null){
			$pagedat = $this->splitParts();

			// This will be a Widget object.
			/** @var Widget_2_1 $c */
			$this->_widget = Widget_2_1::Factory($pagedat['controller']);

			if($this->_widget === null){
				$this->_widget = false;
			}
			else{
				// Make sure it's linked
				$this->_widget->_instance = $this;

				// Was installable passed in?  It may contain data useful to the widget.
				if($this->get('installable')){
					$this->_widget->_installable = $this->get('installable');
				}

				// Pass in any base and customer parameters
				$this->_widget->_params = $pagedat['parameters'];
			}
		}

		return $this->_widget === false ? null : $this->_widget;
	}


	/**
	 * Split a base url into its corresponding parts, controller method and parameters.
	 *
	 * This ONLY supports widgets, and therefore does not support standard Controllers now rewriteurls.
	 *
	 * @param string $base
	 *
	 * @return array
	 */
	public static function SplitBaseURL($base) {

		if (!$base) return null;

		// Trim off both beginning and trailing slashes.
		$base = trim($base, '/');


		$args = null;
		// Support additional arguments
		if (($qpos = strpos($base, '?')) !== false) {
			$argstring = substr($base, $qpos + 1);
			preg_match_all('/([^=&]*)={0,1}([^&]*)/', $argstring, $matches);
			$args = array();
			foreach ($matches[1] as $k => $v) {
				if (!$v) continue;
				$args[$v] = $matches[2][$k];
			}
			$base = substr($base, 0, $qpos);
		}

		// Logic for the Controller.
		$posofslash = strpos($base, '/');

		if ($posofslash) $controller = substr($base, 0, $posofslash);
		else $controller = $base;

		// Preferred way of handling widget names.
		if (class_exists($controller . 'Widget')) {
			switch (true) {
				// 2.1 API
				case is_subclass_of($controller . 'Widget', 'Widget_2_1'):
					// 1.0 API
				case is_subclass_of($controller . 'Widget', 'Widget'):
					$controller = $controller . 'Widget';
					break;
				default:
					// Not a valid widget
					return null;
			}
		}
		// Not quite preferred way, but still works.
		elseif (class_exists($controller)) {
			if(!
				(is_subclass_of($controller, 'Widget_2_1') || is_subclass_of($controller, 'Widget'))
			){
				// Not a valid widget
				return null;
			}
		}
		else {
			// Not even found!
			return null;
		}


		// Trim the base.
		if ($posofslash !== false) $base = substr($base, $posofslash + 1);
		else $base = false;

		// Logic for the Method.
		if ($base) {

			$posofslash = strpos($base, '/');

			// The method can be extended.
			// This means that a method can be in the format of Sites/Edit, which should resolve to Sites_Edit.
			// This only taks effect if the method exists on the controller.
			if ($posofslash) {
				$method = str_replace('/', '_', $base);
				while (!method_exists($controller, $method) && strpos($method, '_')) {
					$method = substr($method, 0, strrpos($method, '_'));
				}
			}
			else {
				$method = $base;
			}

			// Now trim the base again based on the length of the method.
			$base = substr($base, strlen($method) + 1);
		}
		else {
			// The controller may have an "Index" controller.  That doesn't need to be explictly called.
			$method = 'index';
		}

		// One last check that the method exists, (because there's only 1 scenerio that checks above)
		if (!method_exists($controller, $method)) {
			return null;
		}


		// Provide some logic for security.
		// Keep any method starting with a '_' private by preventing
		// direct access from the browser.
		if ($method{0} == '_') return null;


		// Logic for the parameters.
		$params = ($base !== false) ? explode('/', $base) : null;


		// Build these onto a base for a standardized callable URL.
		$baseurl = '/' . ((strpos($controller, 'Widget') == strlen($controller) - 6) ? substr($controller, 0, -6) : $controller);
		// No need to add a method if it's the index.
		if (!($method == 'index' && !$params)) $baseurl .= '/' . str_replace('_', '/', $method);
		$baseurl .= ($params) ? '/' . implode('/', $params) : '';

		// Merge in the named parameters that were extracted from above.
		if($args){
			$params = ($params) ? array_merge($params, $args) : $args;
		}

		return array('controller' => $controller,
		             'method'     => $method,
		             'parameters' => $params,
		             'baseurl'    => $baseurl);
	}

}
