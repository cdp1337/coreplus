<?php
/**
 * Handles all the integration of the TinyMCE plugin.
 *
 * @package TinyMCE-Enterprise
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license All rights reserved
 */

abstract class TinyMCE {

	public static function IncludeTinyMCE(){
		ComponentHandler::LoadScriptLibrary('jquery');
		CurrentPage::AddScript('js/tiny_mce/jquery.tinymce.js');

		// Yes, the string needs quotes inside of quotes!  It's to be read by javascript after all.
		$filebrowsercallback = (\Core\user()->checkAccess('p:/tinymce/imagebrowser/access')) ? "'Core.TinyMCE.FileBrowserCallback'" : 'null';

		$loc = Core::ResolveAsset('js/tiny_mce/tiny_mce.js');

		$script = <<< EOD
<script type="text/javascript">

	Core.TinyMCEDefaults = {
		// Location of TinyMCE script
		script_url : '$loc',

		// General options
		theme : "advanced",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlinkcoreplus,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,|,insertdate,inserttime",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,pagebreak,|,preview,fullscreen,cleanup,code",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Required to not mungle links.
		convert_urls: false,

		// Enterprise features
		file_browser_callback: $filebrowsercallback,

		// Example content CSS (should be your site CSS)
		//content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		//template_external_list_url : "lists/template_list.js",
		//external_link_list_url : "lists/link_list.js",
		//external_image_list_url : "lists/image_list.js",
		//media_external_list_url : "lists/media_list.js",

		// Replace values for the template plugin
		//template_replace_values : {
		//	username : "Some User",
		//	staffid : "991234"
		//}

		__dummy: null
	};

	$(function(){
		$('textarea.tinymce').tinymce(Core.TinyMCEDefaults);
	});
</script>	
EOD;
		// Add the necessary script
		CurrentPage::AddScript($script, 'foot');
		CurrentPage::AddScript('assets/js/tiny_mce/coreplus_functions.js', 'foot');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}
