<?php

function smarty_function_file_thumbnail($params, $template){
	
	// Key/value array of attributes for the resulting HTML.
	$attributes = array();
	
	if(!isset($params['file'])){
		throw new SmartyException('Required parameter [file] not provided for file_thumbnail!');
	}
	
	// Some optional parameters, (and their defaults)
	$size = $d = $assign = $width = $height = false;
	
	
	$file = $params['file'];
	unset($params['file']);
	
	// $file should be a File...
	if(!$file instanceof File_Backend){
		throw new SmartyException('Invalid parameter [file] for file_thumbnail, must be a File_Backend!');
	}
	
	if(isset($params['assign'])){
		$assign = $params['assign'];
		unset($params['assign']);
	}
	
	if(isset($params['width'])){
		$width = $params['width'];
		unset($params['width']);
	}
	
	if(isset($params['height'])){
		$height = $params['height'];
		unset($params['height']);
	}
	
	if(isset($params['dimensions'])){
		$d = $params['dimensions'];
		
		if(is_numeric($d)){
			$width = $d;
			$height = $d;
		}
		else{
			// New method. Split on the "x" and that should give me the width/height.
			$vals = explode('x', strtolower($d));
			$width = (int)$vals[0];
			$height = (int)$vals[1];
		}
		// Translate this dimension set to a "sm/med/lg" size.
		$size = Core::TranslateDimensionToPreviewSize($width, $height);
		unset($params['dimensions']);
	}
	
	if(isset($params['size'])){
		$size = $params['size'];
		// Let size override width and height.
		$width = $height = ConfigHandler::GetValue('/theme/filestore/preview-size-' . $size);
		$d = $width . 'x' . $height;
		unset($params['size']);
	}
	
	
	// If one is provided but not the other, just make them the same.
	if(!$d){
		if($width && !$height) $height = $width;
		if($height && !$width) $width = $height;

		$d = ($width && $height) ? $width . 'x' . $height : false;
		$size = Core::TranslateDimensionToPreviewSize($width, $height);
	}
	
	
	if(!$file->exists()){
		$icon = Core::File('assets/mimetype_icons/notfound-' . $size . '.png');
		$attributes['src'] = $icon->getURL();
	}
	elseif(ConfigHandler::GetValue('/core/filestore/previews') && $file->isPreviewable()){
		if($file->getFilesize() < (1024*1024*4)){
			// Files that are smaller than a certain size can probably be safely rendered on this pageload.
			$attributes['src'] = $file->getPreviewURL($d);
		}
		else{
			// Larger files should be rendered independently.
			// This causes each image to be longer, but should not cause a script timeout.
			$attributes['src'] = Core::ResolveLink('/File/Preview/' . $file->getFilenameHash() . '?size=' . $d);
		}
	}
	else{
		$icon = Core::File('assets/mimetype_icons/' . str_replace('/', '-', strtolower($file->getMimetype()) ) . '-' . $size . '.png');
		if(!$icon->isReadable()) $icon = Core::File('assets/mimetype_icons/unknown-' . $size . '.png');
		$attributes['src'] = $icon->getURL();
	}
	
	// Do the rest of the attributes that the user sent in (if there are any)
	foreach($params as $k => $v){
		$attributes[$k] = $v;
	}
	
	// Merge them back together in one string.
	$html = '<img';
	foreach($attributes as $k => $v) $html .= " $k=\"$v\"";
	$html .= '/>';
	
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
	
	return $assign ? $template->assign($assign, $html) : $html;
}
