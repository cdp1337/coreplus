<?php
// @todo 2012.05.11 cpowell - Can I kill this file?  It doesn't seem to be doing anything.

/**
 * Description of DirectoryController
 *
 * @author powellc
 */
class DirectoryController extends Controller {
	public static function Index(View $page){
		$dir = $page->getParameter('directory');
		if(!$dir){
			$page->error = View::ERROR_BADREQUEST;
			return;
		}
		
		////// Security checks...
		
		// Usage of '..' is explicitly denied, as it can escape the filesystem.
		if(strpos($dir, '../') !== false){
			$page->error = View::ERROR_BADREQUEST;
			return;
		}
		
		// Directory must contain at least one directory in.
		// And it also must start with public/
		if(!preg_match('/^public\/[a-z0-9]+/', $dir)){
			$page->error = View::ERROR_BADREQUEST;
			return;
		}
		
		// Now I can finally start the actual logic.
		$d = Core::Directory($dir);
		if(!$d->isReadable()){
			$page->error = View::ERROR_NOTFOUND;
			return;
		}
		
		$page->assign('files', $d->ls());
	}
}

?>
