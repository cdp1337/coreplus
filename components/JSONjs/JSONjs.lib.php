<?php

/**
 * Description of JSONjs
 *
 * @author powellc
 */
abstract class JSONjs {
	
	public static function IncludeJS(){
		if(ConfigHandler::GetValue('/core/javascript/minified')) CurrentPage::AddScript ('js/json2-min.js');
		else CurrentPage::AddScript ('js/json2.js');
		
		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}

?>
