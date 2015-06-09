<?php
/**
 * Defines the schema for the WidgetInstance table
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

/**
 * Model for WidgetInstanceModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 2011-06-09 01:14:48
 */
class WidgetInstanceModel extends Model {
	/** @var Widget_2_1|null */
	private $_widget;

	public static $Schema = [
		'id'         => [
			'type'     => Model::ATT_TYPE_ID,
			'required' => true,
			'null'     => false,
		],
		'site' => [
			'type' => Model::ATT_TYPE_SITE,
			'default' => 0,
			'formtype' => 'system',
			'comment' => 'The site id in multisite mode, (or 0 otherwise)',
		],
		'baseurl'    => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required'  => true,
			'null'      => false,
			'comment' => 'BaseURL of the widget on this installed instance',
		    'formtype' => 'system',
		],
		'template' => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null'      => true,
			'required'  => false,
			'comment'   => 'The template name on which to display on (for skin-level AND page-level widgets)',
			'default' => null,
			'formtype' => 'system',
		],
		'skin'   => [
			'type' => Model::ATT_TYPE_ALIAS,
			'alias' => 'template',
		],
		'page_baseurl' => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null'      => true,
			'required'  => false,
			'comment'   => 'The page baseurl on which to display on.',
			'default' => null,
			'formtype' => 'system',
		],
		'widgetarea' => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null'      => false,
			'required'  => true,
			'formtype' => 'system',
		],
		'weight'     => [
			'type'    => Model::ATT_TYPE_INT,
			'default' => '10',
			'null'    => false,
		    'formtype' => 'hidden',
		],
		'display_template' => [
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
			'comment' => 'Allows the specific widget template to be overridden.',
			'form' => [
				'type' => 'select',
				'title' => 'Alternative Widget Template',
				//'group' => 'Basic',
				//'grouptype' => 'tabs',
			    'source' => 'this::getAlternativeTemplateOptions'
			],
		],
		'access'     => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'default'   => '*',
			'comment'   => '',
			'null'      => false,
		    'formtype' => 'access'
		],
		'container'  => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default'   => null,
			'null'      => true,
		    'formtype' => 'disabled',
		    // @todo Reserved for future use
		],
		'updated'    => [
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		],
		'created'    => [
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		],
	];

	public static $Indexes = [
		'primary' => ['id'],
	];

	public function __construct($key = null){
		$this->_linked = [
			'Widget' => [
				'link' => Model::LINK_BELONGSTOONE,
				'on' => 'baseurl'
			],
		];

		parent::__construct($key);
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

			// Make sure it's linked
			$this->_widget->_instance = $this;

			// Was installable passed in?  It may contain data useful to the widget.
			if($this->get('installable')){
				$this->_widget->_installable = $this->get('installable');
			}

			// Pass in any base and customer parameters
			$this->_widget->_params = $pagedat['parameters'];
		}

		return $this->_widget;
	}

	/**
	 * Get an array of alternative display templates for this instance.
	 *
	 * This is based on the widget's baseurl.
	 *
	 * @return array
	 */
	public function getAlternativeTemplateOptions(){
		$parts = $this->splitParts();

		// Figure out the template directory for custom pages, (if it exists)
		// In order to get the types, I need to sift through all the potential template directories and look for a directory
		// with the matching name.
		$tmpname = 'widgets' . strtolower('/' . substr($parts['controller'], 0, -6) . '/' . $parts['method']);

		$matches = [];

		foreach(\Core\Templates\Template::GetPaths() as $d){
			if(is_dir($d . $tmpname)){
				// Yay, sift through that and get the files!
				$dir = \Core\Filestore\Factory::Directory($d . $tmpname);
				foreach($dir->ls('tpl') as $file){
					// Skip directories
					if($file instanceof \Core\Filestore\Directory) continue;

					/** @var $file \Core\Filestore\File */
					//$fullpath = $tmpname . $file->getBaseFilename();
					$name = $fullpath = $file->getBaseFilename();
					// Do some template updates and make it a little more friendlier to read.
					$name = ucwords(str_replace('-', ' ', substr($name, 0, -4))) . ' Template';
					$matches[ $fullpath ] = $name;
				}
			}
		}

		return ['' => '-- Default Template --'] + $matches;
	}

	/**
	 * Execute the controller and method this widget request points to.
	 *
	 * @param array $parameters Array of custom parameters passed in from the template
	 *
	 * @return View
	 */
	public function execute($parameters = []) {
		$pagedat = $this->splitParts();
		/// A few sanity/security checks for the controller's sake.

		// The controller must exist first!
		// (note, the SplitParts logic already takes care of the "Is this a valid controller" logic)
		if (!$pagedat['controller']) {
			$view        = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}

		// Any method that starts with a "_" is an internal-only method!
		if ($pagedat['method']{0} == '_') {
			$view        = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}

		// It also must be a part of the class... obviously
		if (!method_exists($pagedat['controller'], $pagedat['method'])) {
			$view        = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}


		$c = $this->getWidget();
		if($c === null){
			$view = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}

		// Pass in any base and customer parameters
		$c->_params = array_merge($c->_params, $parameters);

		$return = call_user_func([$c, $pagedat['method']]);
		if (is_int($return)) {
			// A generic error code was returned.  Create a View with that code and return that instead.
			$view        = new View();
			$view->error = $return;
			return $view;
		} elseif ($return === null) {
			// Hopefully it's setup!
			$return = $c->getView();
		}
		elseif ($return == '') {
			return '';
		}
		elseif (is_string($return)) {
			return $return;
		}
		// No else needed, else it's a valid object.


		// Try to guess the templatename if it wasn't set.
		if ($return->error == View::ERROR_NOERROR && $return->contenttype == View::CTYPE_HTML && $return->templatename === null) {
			$cnameshort           = (strpos($pagedat['controller'], 'Widget') == strlen($pagedat['controller']) - 6) ? substr($pagedat['controller'], 0, -6) : $pagedat['controller'];

			if($this->get('display_template')){
				$return->templatename = strtolower('/widgets/' . $cnameshort . '/' . $pagedat['method'] . '/' . $this->get('display_template'));
			}
			else{
				$return->templatename = strtolower('/widgets/' . $cnameshort . '/' . $pagedat['method'] . '.tpl');
			}
		}

		return $return;
	}

} // END class WidgetInstanceModel extends Model
