<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


abstract class jPlayer {
	public static function IncludeJPlayer(){
		ComponentHandler::LoadScriptLibrary('jquery');
		CurrentPage::AddScript('js/jquery.jplayer.min.js');
		CurrentPage::AddStylesheet('css/skin/jplayer.blue.monday.css');
	
		//Can has include?
		return true;
	}
	
}


?>