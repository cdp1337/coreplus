<?php

function smarty_function_widgetarea($params, $template){
	// Get all widgets set to load in this area.
	
	$body = '';
	$name = $params['name'];
	
	// @todo Add support for per-page widgets.
	
	// Pages can have their own template for this theme.
	$template = PageRequest::GetSystemRequest()->getPageModel()->get('theme_template');
	if(!$template) $template = ConfigHandler::Get('/theme/default_template');
	
	$theme = ConfigHandler::Get('/theme/selected');
	
	$wifac = WidgetInstanceModel::Find(array('theme' => $theme, 'template' => $template, 'widgetarea' => $name), null, 'weight');
	foreach($wifac as $wi){
		// User cannot access this widget? Don't display it...
		if(!\Core\user()->checkAccess($wi->get('access'))) continue;
		
		$view = $wi->execute();
		
		$contents = ($view->error == View::ERROR_NOERROR) ? $view->fetch() : ('Error displaying widget: [' . $view->error . ']');
		
		$body .= '<div class="widget">' . $contents . '</div>';
	}
	
	// Do some sanitizing for the css data
	$class = 'widgetarea-' . strtolower(str_replace(' ', '', $name));
	
	return '<aside class="widgetarea ' . $class . '" widgetarea="' . $name . '">' . $body . '</aside>';
}
