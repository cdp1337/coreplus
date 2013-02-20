<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 9/12/12
 * Time: 1:39 PM
 * To change this template use File | Settings | File Templates.
 */
class TinyMCEController extends Controller_2_1 {
	/**
	 * Get the rendered HTML template for the advlink plugin.
	 *
	 * This needs to be a full controller because it requires some of core+'s functionality to determine pages.
	 */
	public function popup_link(){
		$view = $this->getView();

		// Since this will deal with mainly frontend data, it's doubtful that the admin would want to list admin pages.
		$pages = PageModel::GetPagesAsOptions('admin = 0');
		// For each page, resolve the url to a full url for this site.  Useful because I cannot guarantee correct
		// resolution after it goes through tinyMCE's logic.
		$pagesresolved = array();
		foreach($pages as $url => $title){
			$pagesresolved[Core::ResolveLink($url)] = $title;
		}

		$tplname = Template::ResolveFile('pages/tinymce/popup/link.phtml');

		$view->overrideTemplate(new TemplatePHTML());

		$view->mastertemplate = false;
		$view->templatename = $tplname;
		$view->assign('pages', $pagesresolved);
	}

	/**
	 * Popup for displaying images on the server.
	 */
	public function image(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$status = $this->_setupFileBrowser($view, $request, 'image');
		return $status;
	}

	/**
	 * Popup for displaying files on the server.
	 */
	public function file(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$status = $this->_setupFileBrowser($view, $request, 'file');
		return $status;
	}

	/**
	 * Helper function for the directory mkdir command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function directory_mkdir(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$status = $this->_setupAjaxRequest($view, $request);
		if($status != View::ERROR_NOERROR) return $status;

		// Get a listing of files in the appropriate directory.
		if(ConfigHandler::Get('/tinymce/imagebrowser/sandbox-user-uploads')){
			$basedirname = 'public/tinymce/' . \Core\user()->get('id') . '/';
		}
		else{
			$basedirname = 'public/tinymce/';
		}

		if(!isset($_POST['dir'])) return View::ERROR_BADREQUEST;
		if(!isset($_POST['newdir'])) return View::ERROR_BADREQUEST;

		if(!trim($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a directory name');
			return;
		}

		if(strpos($_POST['newdir'], '/') !== false){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid directory name');
			return;
		}

		$dirname = $basedirname . str_replace('..', '', $_POST['dir']);

		$newdir = $_POST['newdir'];
		// Replace ".." with blank
		$newdir = str_replace('..', '', $newdir);
		// Replace spaces with a dash, makes them more web friendly
		$newdir = str_replace(' ', '-', $newdir);

		$newdirname = $dirname . '/' . $newdir;

		$dir = new Directory_local_backend($newdirname);
		if($dir->exists()){
			$view->jsondata = array('status' => 0, 'message' => 'Directory already exists');
			return;
		}

		$dir->mkdir();

		if($dir->exists()){
			$view->jsondata = array('status' => 1, 'message' => 'Created directory successfully');
			return;
		}
		else{
			$view->jsondata = array('status' => 0, 'message' => 'Unable to create directory');
			return;
		}
	}

	/**
	 * Helper function for the directory rename command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function directory_rename(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$status = $this->_setupAjaxRequest($view, $request);
		if($status != View::ERROR_NOERROR) return $status;

		// Get a listing of files in the appropriate directory.
		if(ConfigHandler::Get('/tinymce/imagebrowser/sandbox-user-uploads')){
			$basedirname = 'public/tinymce/' . \Core\user()->get('id') . '/';
		}
		else{
			$basedirname = 'public/tinymce/';
		}

		if(!isset($_POST['dir'])) return View::ERROR_BADREQUEST;
		if(!isset($_POST['olddir'])) return View::ERROR_BADREQUEST;
		if(!isset($_POST['newdir'])) return View::ERROR_BADREQUEST;

		if(!trim($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a directory name');
			return;
		}

		if(strpos($_POST['newdir'], '/') !== false){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid directory name');
			return;
		}

		$newdir = $_POST['newdir'];
		// Replace ".." with blank
		$newdir = str_replace('..', '', $newdir);
		// Replace spaces with a dash, makes them more web friendly
		$newdir = str_replace(' ', '-', $newdir);

		$dirname = $basedirname . str_replace('..', '', $_POST['olddir']);
		$dir = new Directory_local_backend($dirname);
		if($dir->rename($newdir)){
			$view->jsondata = array('status' => 1, 'message' => 'Renamed directory successfully');
			return;
		}
		else{
			$view->jsondata = array('status' => 0, 'message' => 'Unable to rename directory');
			return;
		}
	}

	/**
	 * Helper function for the directory delete command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function directory_delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$status = $this->_setupAjaxRequest($view, $request);
		if($status != View::ERROR_NOERROR) return $status;

		// Get a listing of files in the appropriate directory.
		if(ConfigHandler::Get('/tinymce/imagebrowser/sandbox-user-uploads')){
			$basedirname = 'public/tinymce/' . \Core\user()->get('id') . '/';
		}
		else{
			$basedirname = 'public/tinymce/';
		}

		if(!isset($_POST['dir'])) return View::ERROR_BADREQUEST;
		if(!isset($_POST['olddir'])) return View::ERROR_BADREQUEST;

		$dirname = $basedirname . str_replace('..', '', $_POST['olddir']);
		$dir = new Directory_local_backend($dirname);

		if(!$dir->exists()){
			$view->jsondata = array('status' => 0, 'message' => 'Cannot remove a directory that does not exist');
			return;
		}

		if($dir->remove()){
			$view->jsondata = array('status' => 1, 'message' => 'Removed directory successfully');
			return;
		}
		else{
			$view->jsondata = array('status' => 0, 'message' => 'Unable to remove directory');
			return;
		}
	}

	/**
	 * Helper function for the file rename command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function file_rename(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$status = $this->_setupAjaxRequest($view, $request);
		if($status != View::ERROR_NOERROR) return $status;

		// Get a listing of files in the appropriate directory.
		if(ConfigHandler::Get('/tinymce/imagebrowser/sandbox-user-uploads')){
			$basedirname = 'public/tinymce/' . \Core\user()->get('id') . '/';
		}
		else{
			$basedirname = 'public/tinymce/';
		}

		if(!isset($_POST['dir'])) return View::ERROR_BADREQUEST;
		if(!isset($_POST['file'])) return View::ERROR_BADREQUEST;
		if(!isset($_POST['newname'])) return View::ERROR_BADREQUEST;

		if(!trim($_POST['newname'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a file name');
			return;
		}

		if(strpos($_POST['newname'], '/') !== false){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid file name');
			return;
		}

		$file = new File_local_backend($basedirname . $_POST['dir'] . '/' . $_POST['file']);

		$newname = $_POST['newname'];
		// Replace some potentially dangerous characters.
		$newname = str_replace(array('/', '..'), '', $newname);
		// Replace spaces with a dash, makes them more web friendly
		$newname = str_replace(' ', '-', $newname);
		// The name will not have the suffix attached, re-add that.
		$newname .= '.' . $file->getExtension();

		if($file->rename($newname)){
			$view->jsondata = array('status' => 1, 'message' => 'Renamed file successfully');
			return;
		}
		else{
			$view->jsondata = array('status' => 0, 'message' => 'Unable to rename file');
			return;
		}
	}

	/**
	 * Helper function for the file delete command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function file_delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$status = $this->_setupAjaxRequest($view, $request);
		if($status != View::ERROR_NOERROR) return $status;

		// Get a listing of files in the appropriate directory.
		if(ConfigHandler::Get('/tinymce/imagebrowser/sandbox-user-uploads')){
			$basedirname = 'public/tinymce/' . \Core\user()->get('id') . '/';
		}
		else{
			$basedirname = 'public/tinymce/';
		}

		if(!isset($_POST['dir'])) return View::ERROR_BADREQUEST;
		if(!isset($_POST['file'])) return View::ERROR_BADREQUEST;

		$file = new File_local_backend($basedirname . $_POST['dir'] . '/' . $_POST['file']);

		if(!$file->exists()){
			$view->jsondata = array('status' => 0, 'message' => 'Cannot remove a file that does not exist');
			return;
		}

		if($file->delete()){
			$view->jsondata = array('status' => 1, 'message' => 'Removed file successfully');
			return;
		}
		else{
			$view->jsondata = array('status' => 0, 'message' => 'Unable to remove file');
			return;
		}
	}

	private function _setupFileBrowser(View $view, PageRequest $request, $type){
		$view->templatename = 'pages/tinymce/browser-icons.tpl';
		$view->mastertemplate = 'blank.tpl';
		$view->record = false;

		$accesspermission = 'p:/tinymce/filebrowser/access';
		$uploadpermission = 'p:/tinymce/filebrowser/upload';
		$sandboxconfig = '/tinymce/filebrowser/sandbox-user-uploads';

		if($type == 'image'){
			$accept = 'image/*';
		}
		else{
			$accept = '*';
		}


		if(!\Core\user()->checkAccess($accesspermission)) return View::ERROR_ACCESSDENIED;

		// Get a listing of files in the appropriate directory.
		if(ConfigHandler::Get($sandboxconfig)){
			$basedirname = 'public/tinymce/' . \Core\user()->get('id') . '/';
		}
		else{
			$basedirname = 'public/tinymce/';
		}


		if($request->getParameter('dir')){
			$dirname = $basedirname . str_replace('..', '', $request->getParameter('dir'));
		}
		else{
			$dirname = $basedirname;
		}

		// This will create a navigatable tree of directory listings for the user.
		$tree = explode('/', substr($dirname, strlen($basedirname)));
		$treestack = '';
		foreach($tree as $k => $v){
			if(!trim($v)){
				unset($tree[$k]);
			}
			else{
				$treestack .= '/' . $v;
				$tree[$k] = array(
					'name' => $v,
					'stack' => $treestack,
				);
			}
		}

		$dir = new Directory_local_backend($dirname);

		// Allow automatic creation of the root directory.
		if($dirname == $basedirname && !$dir->exists()){
			$dir->mkdir();
		}

		if(!$dir->exists()) return View::ERROR_NOTFOUND;

		$dirlen = strlen($dir->getPath());
		$directories = array();
		$files = array();
		foreach($dir->ls() as $file){
			if($file instanceof Directory_local_backend){
				// Give me a count of children in that directory.  I need to do the logic custom here because I only want directories and imgaes.
				$count = 0;
				foreach($file->ls() as $subfile){
					if($file instanceof Directory_local_backend){
						$count++;
					}
					elseif(($file instanceof File_local_backend)){
						if($type == 'image' && $file->isImage() || $type == 'file') $count++;
					}
				}

				$directories[$file->getBasename()] = array(
					'object' => $file,
					'name' => $file->getBasename(),
					'browsename' => substr($file->getPath(), $dirlen),
					'children' => $count,
				);

			}
			elseif($file instanceof File_local_backend){
				// I only want images
				if($type == 'image' && !$file->isImage()) continue;

				$files[$file->getBaseFilename()] = array(
					'object' => $file,
					'name' => $file->getBaseFilename(),
					'selectname' => $file->getURL(),
				);
			}
		}

		// Sorting would be nice!
		ksort($directories);
		ksort($files);

		// Size presets
		$size = \Core\user()->get('/tinymce/filebrowser/size');
		if(!$size) $size = 'lg';

		switch($size){
			case 'sm':
				$sizepx = 16;
				break;
			case 'med':
				$sizepx = 32;
				break;
			case 'lg':
				$sizepx = 64;
				break;
			case 'xl':
				$sizepx = 128;
				break;
			default:
				$size = 'lg';
				$sizepx = 64;
		}

		// Only certain people are allowed the rights to upload here.
		if(\Core\user()->checkAccess($uploadpermission)){
			$uploadform = new Form();
			$uploadform->set('action', Core::ResolveLink('/tinymce/' . $type . '/upload'));
			$uploadform->addElement(
				'multifile',
				array(
					'basedir' => $dirname,
					'title' => 'Upload Files',
					'name' => 'files',
					'accept' => $accept
				)
			);
			//$uploadform->addElement('submit', array('value' => 'Bulk Upload'));
		}
		else{
			$uploadform = false;
		}

		// Give me some useful tips to show the user.
		$tips = array(
			'You can drag and drop files from your local machine to upload them!',
			'You can double click on a directory to browse that directory.',
			'You can single click on a file or directory to view more information about that file.',
			'You can use the direction arrows on your keyboard to navigate between files!',
			'Pressing ESCAPE will deselect any selected files.',
			'You are free to rename or delete files at will, but be aware, you may delete a file you need!',
		);


		$view->assign('directories', array_values($directories));
		$view->assign('files', array_values($files));
		$view->assign('size', $size);
		$view->assign('sizepx', $sizepx);
		$view->assign('location_tree', $tree);
		$view->assign('location', $treestack);
		$view->assign('tip', $tips[rand(0, sizeof($tips)-1)]);
		$view->assign('uploadform', $uploadform);

		return View::ERROR_NOERROR;
	}


	/**
	 * Internal shortcut function to setup ajax requests
	 *
	 * @param View        $view
	 * @param PageRequest $request
	 *
	 * @return int
	 */
	private function _setupAjaxRequest(View $view, PageRequest $request){
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		if(!\Core\user()->checkAccess('p:/tinymce/imagebrowser/access')) return View::ERROR_ACCESSDENIED;
		if(!\Core\user()->checkAccess('p:/tinymce/imagebrowser/upload')) return View::ERROR_ACCESSDENIED;

		// Meant to be an AJAX POST page only.
		if(!$request->isPost()) return View::ERROR_BADREQUEST;
		if(!$request->isAjax()) return View::ERROR_BADREQUEST;

		// Otherwise, it goes through.
		return View::ERROR_NOERROR;
	}
}
