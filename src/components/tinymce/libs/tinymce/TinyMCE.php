<?php
/**
 * Handles all the integration of the TinyMCE plugin.
 *
 * @package TinyMCE-Enterprise
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
 * @license All rights reserved
 */

namespace TinyMCE;

abstract class TinyMCE {

	public static function IncludeTinyMCE(){
		\ComponentHandler::LoadScriptLibrary('jquery');
		/** @var \View $view */
		$view = \Core\view();
		/** @var UserModel $user */
		$user = \Core\user();

		$view->addScript('js/tinymce/jquery.tinymce.min.js');
		$view->addStylesheet('css/tinymce/overrides.css');

		// Yes, the string needs quotes inside of quotes!  It's to be read by javascript after all.
		$browsable           = ( \Core::IsComponentAvailable('media-manager') && $user->checkAccess('p:/mediamanager/browse') );
		$filebrowsercallback = $browsable ? "Core.TinyMCE.FileBrowserCallback" : 'null';

		$loc = \Core\resolve_asset('js/tinymce/tinymce.min.js');
		$content = \Core\resolve_asset('css/tinymce/content.css');

		$pages = \PageModel::GetPagesAsOptions();
		$links = [];
		foreach($pages as $url => $title){
			// Trim off the "(...)" at the end of the title.
			// Core adds that as a benefit for knowing
			$links[] = [
				'title' => html_entity_decode(preg_replace('/(.*) \([^\)]*\)/', '$1', $title)),
			    'value' => \Core\resolve_link($url),
			];
		}
		// And json the data.
		$links = json_encode($links);

		$script = <<< EOD
<script type="text/javascript">

	Core.TinyMCEDefaults = {
		// Location of TinyMCE script
		script_url : '$loc',

		// General options

		plugins: [
			"advlist anchor autolink",
			"charmap code colorpicker contextmenu",
			"fullscreen",
			"hr",
			"image imagetools insertdatetime",
			"link lists",
			"media",
			"pagebreak paste preview",
			"searchreplace",
			"table textcolor",
			"visualblocks visualchars",
			"wordcount"
	    ],
	    toolbar: "undo redo | styleselect | forecolor backcolor bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",

		theme : "modern",

		// Required to not mungle links.
		convert_urls: false,

		// Requires to support <script/> tags.
		extended_valid_elements : "script[language|type|src]",

		// Core Media Manager integration
		file_browser_callback: $filebrowsercallback,

		// Example content CSS (should be your site CSS)
		content_css : "$content",

		// Drop lists for link/image/media/template dialogs
		//template_external_list_url : "lists/template_list.js",
		//external_link_list_url : "lists/link_list.js",
		//external_image_list_url : "lists/image_list.js",
		//media_external_list_url : "lists/media_list.js",
		 link_list: $links,

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
		$view->addScript('assets/js/tinymce/coreplus_functions.js', 'head');
		$view->addScript($script, 'foot');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}
