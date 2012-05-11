<?php
// @todo 2012.05.11 cpowell - Can I kill this file?  It doesn't seem to be doing anything.

/**
 * Description of FileController
 *
 * @author powellc
 */
class FileController extends Controller{
	public static function Index(View $page){
		
	}
	
	public static function Preview(View $page){
		$filename = $page->getParameter(0);
		
		$page->mode = View::MODE_NOOUTPUT;
		$page->response['contenttype'] = 'image/png';
		
		// A file must have been requested to preview
		if(!$filename){
			$page->error = View::ERROR_BADREQUEST;
			return;
		}
		
		// File must be a base64 encoded string.
		if(strpos($filename, 'base64:') !== 0){
			$page->error = View::ERROR_BADREQUEST;
			return;
		}
		
		// Resolve the base64 data to its actual filename.
		$filename = base64_decode(substr($filename, 7));
		
		// For security reasons, only allow "public" resources to be previewed.
		if(!preg_match('/^public\/[a-z0-9]+/', $filename)){
			$page->error = View::ERROR_BADREQUEST;
			return;
		}
		
		$file = Core::File($filename);
		
		if(!$file->isReadable()){
			$page->error = View::ERROR_NOTFOUND;
			return;
		}
		
		
		$size = $page->getParameter('size')? strtolower($page->getParameter('size')) : 'med';
		// $size has a few limitations...
		switch($size){
			case 's':
			case 'sm':
			case 'small':
				$size = 'sm';
				break;
			defualt:
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
		}
		
		// Flag to tell if this file can even be previewed to begin with.
		$preview = false; // Default to explicit.
		
		
		if(ConfigHandler::Get('/core/filestore/previews') && $file->isPreviewable()){
			$d = ConfigHandler::Get('/theme/filestore/preview-size-' . $size);
			$file->displayPreview($d, false);
		}
		else{
			$icon = Core::File('assets/mimetype_icons/' . str_replace('/', '-', $file->getMimetype()) . '-' . $size . '.png');
			if(!$icon->isReadable()) $icon = Core::File('assets/mimetype_icons/unknown-' . $size . '.png');
			echo $icon->getContents();
		}
	}
}
