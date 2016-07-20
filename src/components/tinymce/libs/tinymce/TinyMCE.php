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
	/** @var array Container for custom plugins for TinyMCE defined by other libraries. */
	public static $CustomPlugins = [];
	
	private static $_Queued = false;

	/**
	 * New method to queue TinyMCE to be loaded after the page has been rendered.
	 * This is used to support custom plugins from other components that may not be present at the time of execution.
	 */
	public static function QueueInclude(){
		self::$_Queued = true;
		return true;
	}
	
	public static function CheckInclude(){
		if(self::$_Queued){
			self::IncludeTinyMCE();
		}
		return true;
	}

	public static function IncludeTinyMCE(){
		\ComponentHandler::LoadScriptLibrary('jquery');
		/** @var \View $view */
		$view = \Core\view();
		/** @var \UserModel $user */
		$user = \Core\user();

		// I need to include both versions of TinyMCE so that
		// 1) the tinymce object is visible in the global scope at the time of execution and
		// 2) so I can target all inputs by their class name instead of the ID.
		$view->addScript('js/tinymce/tinymce.min.js');
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
		
		// Create the list of plugins
		// Start with standard and tack on any custom ones.
		$plugins = [
			'advlist',
			'anchor',
			'autolink',
			'charmap',
			'code',
			'colorpicker',
			'contextmenu',
			'fullscreen',
			'hr',
			'image',
			'imagetools',
			'insertdatetime',
			'link',
			'lists',
			'media',
			'pagebreak',
			'paste',
			'preview',
			'searchreplace',
			'table',
			'textcolor',
			'visualblocks',
			'visualchars',
			'wordcount'
		];
		
		$customIncludes = '';
		foreach(self::$CustomPlugins as $name => $src){
			// The "-" is required to inform TinyMCE not to load the plugin again.
			// It'll be loaded manually via the .load() method as it has a custom URL.
			$plugins[] = '-' . $name;
			// Resolve this src to an absolute URL
			$src = \Core\resolve_asset($src);
			
			$customIncludes .= 'tinymce.PluginManager.load("' . $name . '", "' . $src . '");';
		}
		
		// And make them something that javascript can understand.
		$plugins = json_encode($plugins);

		$script = <<< EOD
<script type="text/javascript">

	Core.TinyMCEDefaults = {
		// Location of TinyMCE script
		script_url : '$loc',
		
		style_formats: [
			{ title: 'Headers', items: [
				{ title: 'h1', block: 'h1' },
				{ title: 'h2', block: 'h2' },
				{ title: 'h3', block: 'h3' },
				{ title: 'h4', block: 'h4' },
				{ title: 'h5', block: 'h5' },
				{ title: 'h6', block: 'h6' }
			] },
			
			{ title: 'Blocks', items: [
				{ title: 'p', block: 'p' },
				{ title: 'div', block: 'div' },
				{ title: 'pre', block: 'pre' }
			] },
			
			{ title: 'Containers', items: [
				{ title: 'section', block: 'section', wrapper: true, merge_siblings: false },
				{ title: 'article', block: 'article', wrapper: true, merge_siblings: false },
				{ title: 'blockquote', block: 'blockquote', wrapper: true },
				{ title: 'hgroup', block: 'hgroup', wrapper: true },
				{ title: 'aside', block: 'aside', wrapper: true },
				{ title: 'figure', block: 'figure', wrapper: true }
			] }
		],

		// General options

		plugins: $plugins,
	    toolbar: "undo redo | styleselect | forecolor backcolor bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",

		theme : "modern",

		// Required to not mungle links.
		convert_urls: false,

		// Requires to support <script/> and HTML5 tags.
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
		$customIncludes
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
