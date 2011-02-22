<?php

function smarty_function_link($params, $template){
	
	$assign= (isset($params['assign']))? $params['assign'] : false;
	
	// I don't really care what the parameter's called to be honest...
	if(isset($params['href'])) $href = $params['href'];
	elseif(isset($params['link'])) $href = $params['link'];
	elseif(isset($params['to'])) $href = $params['to'];
	
	$href = Core::ResolveLink($href);
	
    return $assign ? $template->assign($assign, $href) : $href;
}
