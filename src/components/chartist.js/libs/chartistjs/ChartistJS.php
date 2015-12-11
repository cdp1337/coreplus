<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 12/7/15
 * Time: 9:32 PM
 */
class ChartistJS {
	public static function IncludeJS(){
		\Core\view()->addScript('js/chartist.js');
		\Core\view()->addStyle('css/chartist.css');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}