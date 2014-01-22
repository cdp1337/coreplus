<?php
/**
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

/**
 * @param $params
 * @param $template
 *
 * @return string
 */
function smarty_function_widgetarea($params, $template) {
	// Get all widgets set to load in this area.

	$body = '';
	$name = $params['name'];
	$page = $template->template_resource;
	// May provide metadata useful for the called widget.... maybe.
	$installable = (isset($params['installable'])) ? $params['installable'] : null;

	// I need to resolve the page template down to the base version in order for the lookup to work.
	foreach(Core\Templates\Template::GetPaths() as $base){
		if(strpos($page, $base) === 0){
			$page = substr($page, strlen($base));
			break;
		}
	}

	// Pages can have their own template for this theme.
	$tplname = \Core\view()->mastertemplate;
	if (!$tplname) $tplname = ConfigHandler::Get('/theme/default_template');

	$theme = ConfigHandler::Get('/theme/selected');

	// Given support for page-level widgets, this logic gets slightly more difficult...
	$factory = new ModelFactory('WidgetInstanceModel');
	$factory->order('weight');
	if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
		$factory->where('site = ' . MultiSiteHelper::GetCurrentSiteID());
	}

	$subwhere = new Core\Datamodel\DatasetWhereClause();
	$subwhere->setSeparator('OR');

	// First, the skin-level where clause.
	$skinwhere = new Core\Datamodel\DatasetWhereClause();
	$skinwhere->setSeparator('AND');
	$skinwhere->addWhere('theme = ' . $theme);
	$skinwhere->addWhere('template = ' . $tplname);
	$skinwhere->addWhere('widgetarea = ' . $name);
	$subwhere->addWhere($skinwhere);

	// And second, the page-level where clause.
	$pagewhere = new Core\Datamodel\DatasetWhereClause();
	$pagewhere->setSeparator('AND');
	$pagewhere->addWhere('page = ' . $page);
	$pagewhere->addWhere('widgetarea = ' . $name);
	$subwhere->addWhere($pagewhere);

	$factory->where($subwhere);


	foreach ($factory->get() as $wi) {
		/** @var $wi WidgetInstanceModel */
		// User cannot access this widget? Don't display it...
		if(!\Core\user()) continue;
		if (!\Core\user()->checkAccess($wi->get('access'))) continue;

		if($installable){
			$wi->set('installable', $installable);
		}
		$view = $wi->execute();

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

		$body .= '<div class="widget">' . $contents . '</div>';
	}

	// Do some sanitizing for the css data
	$class = 'widgetarea-' . strtolower(str_replace(' ', '', $name));

	return '<div class="widgetarea ' . $class . '" widgetarea="' . $name . '">' . $body . '</div>';
}
