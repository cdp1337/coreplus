<?php

/**
 * Helper function to load the jquery contextmenu plugin.
 */
class JQueryContextMenu {
    public static function Load(){

	    // jqueryui is a dependency.
	    JQuery::IncludeJQueryUI();

        CurrentPage::AddScript ('js/jquery/jquery.contextMenu.js');
	    CurrentPage::AddStylesheet('css/jquery.contextMenu.css');

        // IMPORTANT!  Tells the script that the include succeeded!
        return true;
    }
}
