<?php
/**
 * Handles all the integration of the TinyMCE plugin.
 *
 * @package TinyMCE-Enterprise
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
 * @license All rights reserved
 */

namespace TinyMCE;

abstract class TinyMCE {

	public static function IncludeTinyMCE(){
		\ComponentHandler::LoadScriptLibrary('jquery');
		\Core\view()->addScript('js/tinymce/jquery.tinymce.min.js');
		\Core\view()->addStylesheet('css/tinymce/overrides.css');

		// Yes, the string needs quotes inside of quotes!  It's to be read by javascript after all.
		$browsable           = ( \Core::IsComponentAvailable('media-manager') && \Core\user()->checkAccess('p:/mediamanager/browse') );
		$filebrowsercallback = $browsable ? "Core.TinyMCE.FileBrowserCallback" : 'null';

		$loc = \Core::ResolveAsset('js/tinymce/tinymce.min.js');
		$content = \Core::ResolveAsset('css/tinymce/content.css');

		$script = <<< EOD
<script type="text/javascript">

	Core.TinyMCEDefaults = {
		// Location of TinyMCE script
		script_url : '$loc',

		// General options

		 plugins: [
	        "advlist autolink lists link image charmap print preview anchor",
	        "searchreplace visualblocks code fullscreen",
	        "insertdatetime media table contextmenu paste",
	        "wordcount"
	    ],
	    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",

		theme : "modern",
		// advlinkcoreplus

		// Required to not mungle links.
		convert_urls: false,

		// Enterprise features
		file_browser_callback: $filebrowsercallback,

		// Example content CSS (should be your site CSS)
		content_css : "$content",

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
		\Core\view()->addScript('assets/js/tinymce/coreplus_functions.js', 'head');
		\Core\view()->addScript($script, 'foot');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}
