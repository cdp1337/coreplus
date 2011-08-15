<?php

/**
 * Manually add a widget onto a template.
 * 
 * @param array $params
 * @param Object $template
 * @return mixed 
 */
function smarty_function_widget($params, $template){
	
	$name = $params['name'];
	$assign= (isset($params['assign']))? $params['assign'] : false;
	
	// Try to look up this requested widget.
	$name .= 'Widget';
	if(!class_exists($name)){
		throw new SmartyException('Unable to locate class [' . $name . '] for requested widget', null, null);
	}
	// @todo Add support for requiring instancing.
	
	$w = new $name();
	$dat = $w->execute()->fetch();
	
	return $assign ? $template->assign($assign, $dat) : $dat;
}
