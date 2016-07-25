<?php
/**
 * @license MIT
 */
class JQueryLightbox {
	public static function Load(){

		// I need jquery UI as a pre-req...
		if(!JQuery::IncludeJQueryUI()){
			return false;
		}
		
		\Core\view()->addScript('js/lightbox.js', 'foot');
		\Core\view()->addStylesheet('css/lightbox.css');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}
