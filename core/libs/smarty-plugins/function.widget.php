<?php

/**
 * Manually add a widget onto a template.
 * 
 * @param array $params
 * @param Object $template
 * @return mixed 
 */
function smarty_function_widget($params, $template){
	
	
	$assign= (isset($params['assign']))? $params['assign'] : false;
	
	// Version 2.0 uses baseurl as the defining call.
	if(isset($params['baseurl'])){
		$api = 2.0;
		$parts = WidgetModel::SplitBaseURL($params['baseurl']);
		$name = $parts['controller'];
		$method = $parts['method'];
	}
	// Version 1.0 uses name.
	elseif(isset($params['name'])){
		$api = 1.0;
		$name = $params['name'];
		// Try to look up this requested widget.
		$name .= 'Widget';
	}
	
	
	if(!class_exists($name)){
		throw new SmartyException('Unable to locate class [' . $name . '] for requested widget', null, null);
	}
	// @todo Add support for requiring instancing.
	
	
	$w = new $name();
	// Version 1.0 API
	if($api == 1.0){
		$dat = $w->execute()->fetch();
	}
	// Version 2.0 API
	elseif($api == 2.0){
		$return = call_user_func(array($w, $method));
		if(is_int($return)){
			throw new SmartyException("widget $name/$method returned error code $return.", null, null);
		}
		elseif($return === null){
			// Hopefully it's setup!
			$return = $w->getView();
		}
		// No else needed, else it's a valid object.
		
		
		// Try to guess the templatename if it wasn't set.
		if($return->error == View::ERROR_NOERROR && $return->contenttype == View::CTYPE_HTML && $return->templatename === null){
			$cnameshort = (strpos($name, 'Widget') == strlen($name) - 6) ? substr($name, 0, -6) : $name;
			$return->templatename = strtolower('/widgets/' . $cnameshort . '/' . $method . '.tpl');
		}
		
		$dat = $return->fetch();
	}
	
	
	return $assign ? $template->assign($assign, $dat) : $dat;
}
