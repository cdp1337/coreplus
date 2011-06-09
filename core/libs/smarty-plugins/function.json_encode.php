<?php

function smarty_function_json_encode($params, $template){
	
	
	if(isset($params['assign'])){
		$assign = $params['assign'];
		unset($params['assign']);
	}
	else{
		$assign = false;
	}
	
	$out = json_encode($params);
	//var_dump($out); die();
	
    return $assign ? $template->assign($assign, $out) : $out;
}
