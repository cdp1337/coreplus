<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


abstract class jPlayer {
	public static function IncludeJPlayer(){
		ComponentHandler::LoadScriptLibrary('jquery');
		\Core\view()->addScript('js/jquery.jplayer.min.js');
		\Core\view()->addStylesheet('css/skin/jplayer.blue.monday.css');
	
		//Can has include?
		return true;
	}
	
}
