<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 9/12/12
 * Time: 2:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Core\Templates;

interface TemplateInterface {
	/**
	 * Fetch fully rendered HTML from this template.
	 *
	 * @param $template string Fully resolved filename of the template to render
	 * @return string HTML
	 * @throws TemplateException
	 */
	public function fetch($template = null);

	/**
	 * Display the fully rendered HTML from this template to the browser.
	 *
	 * @param $template string Fully resolved filename of the template to render
	 * @return void
	 * @throws TemplateException
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
}
