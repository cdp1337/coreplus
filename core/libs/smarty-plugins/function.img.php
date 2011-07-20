<?php

function smarty_function_img($params, $template){
	
	// Key/value array of attributes for the resulting HTML.
	$attributes = array();
	
	// Generally "src" is given.
	
	if(!isset($params['src'])){
		throw new SmartyException('{img} tag requires a src.');
	}
	
	// Some optional parameters, (and their defaults)
	$assign = $width = $height = false;
	
	if(isset($params['assign'])){
		$assign = $params['assign'];
		unset($params['assign']);
	}
	
	if(isset($params['width'])){
		$width = $params['width'];
		unset($params['width']);
	}
	
	if(isset($params['height'])){
		$height = $params['height'];
		unset($params['height']);
	}
	
	
	// If one is provided but not the other, just make them the same.
	if($width && !$height) $height = $width;
	if($height && !$width) $width = $height;
	
	$d = ($width && $height) ? $width . 'x' . $height : false;
	
	// Well... 
	$f = Core::File($params['src']);
	$attributes['src'] = $f->getPreviewURL($d);
	unset($params['src']);
	
	// Do the rest of the attributes that the user sent in (if there are any)
	foreach($params as $k => $v){
		$attributes[$k] = $v;
	}
	
	// Merge them back together in one string.
	$html = '<img';
	foreach($attributes as $k => $v) $html .= " $k=\"$v\"";
	$html .= '/>';
	
    return $assign ? $template->assign($assign, $html) : $html;
}