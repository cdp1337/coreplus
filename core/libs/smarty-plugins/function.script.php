<?php

function smarty_function_script($params, $template){
	
	ComponentHandler::LoadScriptLibrary($params['name']);
}
