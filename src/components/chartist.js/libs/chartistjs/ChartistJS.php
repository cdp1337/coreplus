<?php

/**
 * @author Charlie Powell <charlie@evalagency.com>
 */
class ChartistJS {
	public static function IncludeJS(){
		\Core\view()->addScript('js/chartist.js');
		\Core\view()->addStylesheet('css/chartist.css');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}