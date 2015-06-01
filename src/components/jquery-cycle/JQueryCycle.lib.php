<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hinschn
 * Date: 7/24/12
 * Time: 10:13 AM
 * To change this template use File | Settings | File Templates.
 */
class JQueryCycle {

	protected static $_IncludedVersion = null;

	public static function Load(){

		JQuery::IncludeJQuery();

		if(self::$_IncludedVersion === null || self::$_IncludedVersion === 1){
			\Core\view()->addScript ('js/jquery.cycle.all.js');
			// IMPORTANT!  Tells the script that the include succeeded!

			self::$_IncludedVersion = 1;
			return true;
		}
		else{
			return false;
		}
	}

	public static function Load2(){

		JQuery::IncludeJQuery();

		if(self::$_IncludedVersion === null || self::$_IncludedVersion === 2){
			\Core\view()->addScript ('js/jquery.cycle2.js');
			// IMPORTANT!  Tells the script that the include succeeded!

			self::$_IncludedVersion = 2;
			return true;
		}
		else{
			return false;
		}
	}
}
