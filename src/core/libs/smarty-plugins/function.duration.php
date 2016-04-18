<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 2/20/16
 * Time: 8:12 PM
 */

function smarty_function_duration($params, $smarty){
	$duration = $params[0];

	return \Core\time_duration_format($duration);
}