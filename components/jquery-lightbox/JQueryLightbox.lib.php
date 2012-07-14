<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/11/12
 * Time: 3:16 PM
 * To change this template use File | Settings | File Templates.
 */
class JQueryLightbox {
	public static function Load(){

		// I need jquery UI as a pre-req...
		if(!JQuery::IncludeJQueryUI()){
			return false;
		}

		if(ConfigHandler::Get('/core/javascript/minified')){
			CurrentPage::AddScript ('js/jquery.lightbox-0.5.min.js');
		}
		else{
			CurrentPage::AddScript ('js/jquery.lightbox-0.5.js');
		}

		CurrentPage::AddStylesheet('css/jquery.lightbox-0.5.css');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}
