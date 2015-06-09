<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
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
 * @todo Finish documentation of smarty_function_widgetarea
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty_Internal_Template $smarty  Parent Smarty template object
 *
 * @return string|void
 */
function smarty_function_widgetarea($params, $smarty) {
	// Get all widgets set to load in this area.

	$body = '';

	$baseurl = PageRequest::GetSystemRequest()->getBaseURL();
	$template = $smarty->template_resource;

	$parameters  = [];
	$name        = null;
	$installable = null;
	$assign      = null;
	foreach($params as $k => $v){
		switch($k){
			case 'name':
				$name = $v;
				break;
			case 'installable':
				$installable = $v;
				break;
			case 'assign':
				$assign = $v;
				break;
			default:
				$parameters[$k] = $v;
				break;
		}
	}

	// I need to resolve the page template down to the base version in order for the lookup to work.
	foreach(Core\Templates\Template::GetPaths() as $base){
		if(strpos($template, $base) === 0){
			$template = substr($template, strlen($base));
		}
	}

	// Given support for page-level widgets, this logic gets slightly more difficult...
	$factory = new ModelFactory('WidgetInstanceModel');
	$factory->order('weight');
	if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
		$factory->whereGroup('or', ['site = -1', 'site = ' . MultiSiteHelper::GetCurrentSiteID()]);
	}

	$subwhere = new Core\Datamodel\DatasetWhereClause();
	$subwhere->setSeparator('OR');

	// First, the skin-level where clause.
	$skinwhere = new Core\Datamodel\DatasetWhereClause();
	$skinwhere->setSeparator('AND');
	$skinwhere->addWhere('template = ' . $template);
	$skinwhere->addWhere('widgetarea = ' . $name);
	$subwhere->addWhere($skinwhere);

	// And second, the page-level where clause.
	if($baseurl){
		$pagewhere = new Core\Datamodel\DatasetWhereClause();
		$pagewhere->setSeparator('AND');
		$pagewhere->addWhere('page_baseurl = ' . $baseurl);
		$pagewhere->addWhere('widgetarea = ' . $name);
		$subwhere->addWhere($pagewhere);
	}

	$factory->where($subwhere);


	$widgetcount = 0;
	try{
		$widgets = $factory->get();
	}
	catch(Exception $e){
		if(DEVELOPMENT_MODE){
			$body .= '<p class="message-error">Exception while trying to load widget area ' . $name . '!</p>';
			$body .= '<pre class="xdebug-var-dump">' . $e->getMessage() . '</pre>';
		}
		else{
			\Core\ErrorManagement\exception_handler($e, false);
		}
		$widgets = [];
		++$widgetcount;
	}


	foreach ($widgets as $wi) {
		/** @var $wi WidgetInstanceModel */
		// User cannot access this widget? Don't display it...
		if(!\Core\user()) continue;
		if (!\Core\user()->checkAccess($wi->get('access'))) continue;

		if($installable){
			$wi->set('installable', $installable);
		}
		$view = $wi->execute($parameters);

		// Some widgets may return simply a blank string.  Those should just be ignored.
		if ($view == '') continue;

		// If it's just a string, return that.
		if (is_string($view)) {
			$contents = $view;
		}
		elseif($view->error == View::ERROR_NOERROR){
			$contents = $view->fetch();
		}
		else{
			$contents = 'Error displaying widget [' . $wi->get('baseurl') . '], returned error [' . $view->error . ']';
		}
		++$widgetcount;

		$body .= '<div class="widget">' . $contents . '</div>';
	}

	// Do some sanitizing for the css data
	$class = 'widgetarea-' . strtolower(str_replace(' ', '', $name));

	$html = '<div class="widgetarea ' . $class . '" widgetarea="' . $name . '">' . $body . '</div>';

	// No widgets, no inner content!
	if($widgetcount == 0){
		$html = '';
	}

	if($assign){
		$smarty->assign($assign, $html);
	}
	else{
		return $html;
	}
}
