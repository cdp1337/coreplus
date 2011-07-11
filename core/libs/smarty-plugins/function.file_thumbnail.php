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
			$size = 'sm';
			break;
		case 'm':
		case 'med':
		case 'medium':
			$size = 'med';
			break;
		case 'l':
		case 'lg':
		case 'large':
			$size = 'lg';
			break;
		case 'xl':
		case 'x-large':
			$size = 'xl';
			break;
		default:
			throw new SmartyException('Invalid parameter [size] for file_thumbnail, must be "sm", "med" or "lg"!');
	}
	
	if(ConfigHandler::GetValue('/core/filestore/previews') && $file->isPreviewable()){
		$d = ConfigHandler::GetValue('/theme/filestore/preview-size-' . $size);
		
		if($file->getFilesize() < (1024*1024*4)){
			// Files that are smaller than a certain size can probably be safely rendered on this pageload.
			$src = $file->getPreviewURL($d);
		}
		else{
			// Larger files should be rendered independently.
			// This causes each image to be longer, but should not cause a script timeout.
			$src = Core::ResolveLink('/File/Preview/' . $file->getFilenameHash() . '?size=' . $size);
		}
	}
	else{
		$icon = Core::File('assets/mimetype_icons/' . str_replace('/', '-', $file->getMimetype()) . '-' . $size . '.png');
		if(!$icon->isReadable()) $icon = Core::File('assets/mimetype_icons/unknown-' . $size . '.png');
		$src = $icon->getURL();
	}
	
	$html = '<img src="' . $src . '"/>';
	/*
	var_dump($file, $file->getFilename(''), $file->getFilenameHash()); die();
	if(ConfigHandler::GetValue('/core/filestore/previews')){
		var_dump($file->getMimetype()); die();
	}
	else{
		$ext = $file->getExtension();
		$icon = Core::ResolveAsset('mimetype_icons/' . $ext . $size . '.png');
		if(!$icon) $icon = Core::ResolveAsset('mimetype_icons/unknown' . $size . '.png');
		
		$html = '<img src="' . $icon . '"/>';
	}
	*/
	
	if(isset($params['assign'])) $template->assign($params['assign'], $html);
	else return $html;
}
