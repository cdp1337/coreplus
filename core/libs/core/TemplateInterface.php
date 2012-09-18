<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 9/12/12
 * Time: 2:02 PM
 * To change this template use File | Settings | File Templates.
 */
interface TemplateInterface {
	/**
	 * Fetch fully rendered HTML from this template.
	 *
	 * @param $template string Fully resolved filename of the template to render
	 * @return string HTML
	 * @throws TemplateException
	 */
	public function fetch($template);

	/**
	 * Display the fully rendered HTML from this template to the browser.
	 *
	 * @param $template string Fully resolved filename of the template to render
	 * @return void
	 * @throws TemplateException
	 */
	public function render($template);

	/**
	 * Returns a single or all template variables
	 *
	 * @param string|null  $varname        variable name or null
	 * @return string|array|null variable value or or array of variables
	 */
    public function getTemplateVars($varname = null);

	/**
	 * Assign a variable into the template
	 *
	 * This is required because templates are sandboxed from the rest of the application.
	 *
	 * @param array|string $tpl_var the template variable name(s)
	 * @param mixed        $value   the value to assign
	 */
	public function assign($tpl_var, $value = null);
}
