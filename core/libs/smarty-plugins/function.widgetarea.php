<?php

function smarty_function_widgetarea($params, $template){
	// Get all widgets set to load in this area.
	
	$body = '';
	$name = $params['name'];
	
	// @todo Add support for per-page widgets.
	
	$wifac = WidgetInstanceModel::Find(array('widgetarea' => $name), null, 'weight');
	foreach($wifac as $wi){
		// User cannot access this widget? Don't display it...
		if(!Core::User()->checkAccess($wi->get('access'))) continue;
		
		$widget = new WidgetModel($wi->get('widgetid'));
		$body .= '<div class="widget" widgetid="' . $wi->get('widgetid') . '" ' .
			'instanceid="' . $wi->get('id') . '" weight="' . $wi->get('weight') . '"' .
			'>' . $widget->getWidget()->execute()->fetch() . '</div>';
	}
	
	
	
	
	// Do some sanitizing for the css data
	$class = 'widgetarea-' . strtolower(str_replace(' ', '', $name));
	
	return '<aside class="widgetarea ' . $class . '" widgetarea="' . $name . '">' . $body . '</aside>';
}
