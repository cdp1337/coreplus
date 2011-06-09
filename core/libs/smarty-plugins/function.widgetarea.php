<?php

function smarty_function_widgetarea($params, $template){
	// Get all widgets set to load in this area.
	
	$body = '';
	$name = $params['name'];
	
	$wifac = WidgetInstanceModel::Find(array('area' => $name, 'theme' => ConfigHandler::GetValue('/core/theme')), null, 'weight');
	foreach($wifac as $wi){
		$widget = new WidgetModel($wi->get('baseurl'));
		$body .= '<div class="widget" baseurl="' . $wi->get('baseurl') . '" ' .
			'instanceid="' . $wi->get('id') . '" weight="' . $wi->get('weight') . '"' .
			'>' . $widget->execute()->fetch() . '</div>';
	}
	
	
	
	
	// Do some sanitizing for the css data
	$class = 'widgetarea-' . strtolower(str_replace(' ', '', $name));
	
	return '<aside class="widgetarea ' . $class . '" widgetarea="' . $name . '">' . $body . '</aside>';
}
