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
	
	// Load in the theme sizes for reference.
	$themesizes = array(
		'sm' => ConfigHandler::GetValue('/theme/filestore/preview-size-sm'),
		'med' => ConfigHandler::GetValue('/theme/filestore/preview-size-med'),
		'lg' => ConfigHandler::GetValue('/theme/filestore/preview-size-lg'),
		'xl' => ConfigHandler::GetValue('/theme/filestore/preview-size-xl'),
	);
	
	if(isset($params['dimensions'])){
		// Try to determine the approximate size of this in correlation to an icon size.
		// Current strings supported are "##" and "##x##"
		
		if(is_numeric($params['dimensions'])){
			// It's a straight single number, use that for both dimensions.
			$width = $params['dimensions'];
			$height = $params['dimensions'];
		}
		elseif(stripos($params['dimensions'], 'x') !== false){
			// It's a string joining both dimensions.
			$ds = explode('x', strtolower($params['dimensions']));
			$width = trim($ds[0]);
			$height = trim($ds[1]);
		}
		else{
			// Invalid dimension given.
			throw new SmartyException('Unable to determine dimensions requested [' . $params['dimensions'] . ']');
		}
		
		$d = $width . 'x' . $height;
		$size = Core::TranslateDimensionToPreviewSize($width, $height);
	}
	elseif(isset($params['size'])){
		switch($size){
			case 's':
			case 'sm':
			case 'small':
				$d = $themesizes['sm'] . 'x' . $themesizes['sm'];
				$size = 'sm';
				break;
			case 'm':
			case 'med':
			case 'medium':
				$d = $themesizes['med'] . 'x' . $themesizes['med'];
				$size = 'med';
				break;
			case 'l':
			case 'lg':
			case 'large':
				$d = $themesizes['lg'] . 'x' . $themesizes['lg'];
				$size = 'lg';
				break;
			case 'xl':
			case 'x-large':
				$d = $themesizes['xl'] . 'x' . $themesizes['xl'];
				$size = 'xl';
				break;
			default:
				// Allow an explicit dimension to be sent in.
				$d = $size;
		}
	}
	else{
		// Default.
		$size = 'med';
		$d = $themesizes['med'] . 'x' . $themesizes['med'];
	}
	
	
	if(ConfigHandler::GetValue('/core/filestore/previews') && $file->isPreviewable()){
		if($file->getFilesize() < (1024*1024*4)){
			// Files that are smaller than a certain size can probably be safely rendered on this pageload.
			$src = $file->getPreviewURL($d);
		}
		else{
			// Larger files should be rendered independently.
			// This causes each image to be longer, but should not cause a script timeout.
			$src = Core::ResolveLink('/File/Preview/' . $file->getFilenameHash() . '?size=' . $d);
		}
	}
	else{
		$icon = Core::File('assets/mimetype_icons/' . str_replace('/', '-', strtolower($file->getMimetype()) ) . '-' . $size . '.png');
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
