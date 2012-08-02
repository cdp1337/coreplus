<?php
/**
 * @package Core Plus\Core
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

	// @todo Add support for per-page widgets.

	// Pages can have their own template for this theme.
	$template = PageRequest::GetSystemRequest()->getPageModel()->get('theme_template');
	if (!$template) $template = ConfigHandler::Get('/theme/default_template');

	$theme = ConfigHandler::Get('/theme/selected');

	$wifac = WidgetInstanceModel::Find(array('theme' => $theme, 'template' => $template, 'widgetarea' => $name), null, 'weight');
	foreach ($wifac as $wi) {
		// User cannot access this widget? Don't display it...
		if (!\Core\user()->checkAccess($wi->get('access'))) continue;

		$view = $wi->execute();

		// Some widgets may return simply a blank string.  Those should just be ignored.
		if ($view == '') continue;

		// If it's just a string, return that.
		if (is_string($view)) {
			$contents = $view;
		} else {
			$contents = ($view->error == View::ERROR_NOERROR) ? $view->fetch() : ('Error displaying widget: [' . $view->error . ']');
		}

		$body .= '<div class="widget">' . $contents . '</div>';
	}

	// Do some sanitizing for the css data
	$class = 'widgetarea-' . strtolower(str_replace(' ', '', $name));

	return '<aside class="widgetarea ' . $class . '" widgetarea="' . $name . '">' . $body . '</aside>';
}
