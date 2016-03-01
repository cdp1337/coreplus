<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 2/20/16
 * Time: 8:12 PM
 */

function smarty_function_duration($params, $smarty){
	$duration = $params[0];
	
	if($duration < 0.001 && $duration > 0){
		return round($duration * 1000, 1) . ' ns';
	}
	elseif($duration < 1 && $duration > 0){
		return round($duration * 1000, 1) . ' ms';
	}
	elseif($duration == 1){
		return '1 second';
	}
	elseif($duration < 60){
		return $duration . ' seconds';
	}
	elseif($duration < 3600){
		$m = round($duration / 60, 0);
		return $m . ($m == 1 ? ' minute' : ' minutes'); 
	}
	else{
		$m = round($duration / 3600, 0);
		return $m . ($m == 1 ? ' hour' : ' hours');
	}
}