<?php

function smarty_function_file_thumbnail($params, $template){
	
	if(!isset($params['file'])){
		throw new SmartyException('Required parameter [file] not provided for file_thumbnail!');
	}
	
	$file = $params['file'];
	
	// $file should be a File...
	if(!$file instanceof File_Backend){
		throw new SmartyException('Invalid parameter [file] for file_thumbnail, must be a File_Backend!');
	}
	
	$size = (isset($params['size']))? strtolower($params['size']) : 'med';
	// $size has a few limitations...
	switch($size){
		case 's':
		case 'sm':
		case 'small':
			$size = '-sm';
			break;
		case 'm':
		case 'med':
		case 'medium':
			$size = '';
			break;
		case 'l':
		case 'lg':
		case 'large':
			$size = '-lg';
			break;
		default:
			throw new SmartyException('Invalid parameter [size] for file_thumbnail, must be "sm", "med" or "lg"!');
	}
	
	if(ConfigHandler::GetValue('/core/filestore/previews')){
		
	}
	else{
		$ext = $file->getExtension();
		$icon = Core::ResolveAsset('mimetype_icons/' . $ext . $size . '.png');
		if(!$icon) $icon = Core::ResolveAsset('mimetype_icons/unknown' . $size . '.png');
		
		$html = '<img src="' . $icon . '"/>';
	}
	
	
	if(isset($params['assign'])) $template->assign($params['assign'], $html);
	else return $html;
}
