<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JQuery
 *
 * @author powellc
 */
abstract class JQuery {
	
	public static function IncludeJQuery(){
		if(ConfigHandler::GetValue('/jquery/minified')) CurrentPage::AddScript ('js/jquery/jquery-1.5.min.js');
		else CurrentPage::AddScript ('js/jquery/jquery-1.5.js');
	}
}

?>
