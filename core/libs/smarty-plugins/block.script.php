<?php

/**
 * Include a script in the main template
 * 
 * Either "name" can be passed to load a library
 * or "script" can be passed to inject a script directly.
 * 
 * @param type $params
 * @param type $template 
 */
function smarty_block_script($params, $innercontent, $template, &$repeat){
	// A script library name is provided.
	if(isset($params['name'])){
		if(!ComponentHandler::LoadScriptLibrary($params['name'])){
			throw new SmartyException('Unable to load script library ' . $params['name']);
		}
	}
	// I guess using "library" to indicate the desired library would make sense too....
	elseif(isset($params['library'])){
		if(!ComponentHandler::LoadScriptLibrary($params['library'])){
			throw new SmartyException('Unable to load script library ' . $params['library']);
		}
	}
	// a script tag can be called with no parameters, it is after all a script tag....
	elseif($innercontent){
		// Does this content have a <script> tag already around it?
		if(strpos($innercontent, '<script') === false){
			$innercontent = '<script type="text/javascript">' . $innercontent . '</script>';
		}
		$loc = (isset($params['location']))? $params['location'] : 'head';
		CurrentPage::AddScript($innercontent, $loc);
	}
}
