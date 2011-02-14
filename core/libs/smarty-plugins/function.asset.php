<?php

function smarty_function_asset($params, $template){
		
	// I don't really care what it's called!
	if(isset($params['file'])) $file = $params['file'];
	elseif(isset($params['src'])) $file = $params['src'];
	elseif(isset($params['href'])) $file = $params['href'];
	
	$f = Core::ResolveAsset($file);
	
	if(isset($params['assign'])) $template->assign($params['assign'], $f);
	else return $f;
}
