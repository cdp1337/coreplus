<?php

/**
 * Helper function to load the jquery contextmenu plugin.
 */
class JQueryContextMenu {
    public static function Load(){

	    // jqueryui is a dependency.
	    JQuery::IncludeJQueryUI();

	    \Core\view()->addScript ('js/jquery/jquery.browser.js');
        \Core\view()->addScript ('js/jquery/jquery.contextMenu.js');
	    \Core\view()->addStylesheet('css/jquery.contextMenu.css');

        // IMPORTANT!  Tells the script that the include succeeded!
        return true;
    }
}
