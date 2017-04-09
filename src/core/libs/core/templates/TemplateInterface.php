<?php
/**
 * [PAGE DESCRIPTION HERE]
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

namespace Core\Templates;

interface TemplateInterface {
	/**
	 * Fetch fully rendered HTML from this template.
	 *
	 * @param $template string Fully resolved filename of the template to render
	 * @return string HTML
	 * @throws Exception
	 */
	public function fetch($template = null);

	/**
	 * Display the fully rendered HTML from this template to the browser.
	 *
	 * @param $template string Fully resolved filename of the template to render
	 * @return void
	 * @throws Exception
	 */
	public function render($template = null);

	/**
	 * Returns a single or all template variables
	 *
	 * @param string|null  $varname        variable name or null
	 * @return string|array|null variable value or or array of variables
	 */
	public function getTemplateVars($varname = null);

	/**
	 * Get a single variable from the template variables.
	 *
	 * @param string $varname The name of the variable
	 *
	 * @return mixed
	 */
	public function getVariable($varname);

	/**
	 * Get the basename of this template
	 *
	 * @return string
	 */
	public function getBasename();

	/**
	 * Get the full filename of this template
	 * 
	 * @return string|null
	 */
	public function getFilename();

	/**
	 * Get the list of optional stylesheets in this template.
	 *
	 * The returned array will be an array of the attributes on the declaration, with at minimum 'src' and 'title'.
	 *
	 * @return array
	 */
	public function getOptionalStylesheets();

	/**
	 * Get an array of widget areas defined on this template.
	 *
	 * The returning array is associative with the widgetarea name as the key,
	 * and each value is an array of name and installable.
	 *
	 * @return array
	 */
	public function getWidgetAreas();

	/**
	 * Get an array of the insertables in this template.
	 *
	 * Should have "name", "type", "title", "value", and "description" in each array.
	 * Should also have any formelement-specific key necessary for operation, ie: "basedir", "accept", etc.
	 *
	 * @return array
	 */
	public function getInsertables();

	/**
	 * Get the registered view for this template, useful for setting CSS and Scripts in correct locations in the markup.
	 *
	 * If no view has been set on this template, then \Core\view() should be returned.
	 *
	 * @return \View
	 */
	public function getView();

	/**
	 * Scan through this template file and see if it has optional stylesheets that the admin can select to enable.
	 *
	 * @return boolean
	 */
	public function hasOptionalStylesheets();

	/**
	 * Scan through this template file and see if it has widgetareas contained within.
	 *
	 * @return boolean
	 */
	public function hasWidgetAreas();

	/**
	 * Assign a variable into the template
	 *
	 * This is required because templates are sandboxed from the rest of the application.
	 *
	 * @param array|string $tpl_var the template variable name(s)
	 * @param mixed        $value   the value to assign
	 */
	public function assign($tpl_var, $value = null);

	/**
	 * Set a template filename to be remembered if fetch or render are called with null parameters.
	 *
	 * @param string $template Filename to remember for this template.
	 *
	 * @return void
	 */
	public function setFilename($template);

	/**
	 * Set the registered view for this template, usually set from the View.
	 *
	 * @param \View $view
	 *
	 * @return void
	 */
	public function setView(\View $view);
}
