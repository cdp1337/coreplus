<?php
/**
 * File for class Navigator definition in the coreplus project
 * 
 * @package TinyMCE
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130603.1802
 * @copyright Copyright (C) 2009-2013  Author
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

namespace TinyMCE;


/**
 * A short teaser of what Navigator does.
 *
 * More lengthy description of what Navigator does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Navigator
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package TinyMCE
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class Navigator {

	public $canupload = false;
	public $canaccess = false;

	/**
	 * @var \View
	 */
	protected $_view;
	protected $_basedir;
	protected $_accept = '*';
	protected $_mode = 'icon';
	protected $_cwd = '';
	protected $_baseurl = '/tinymcenavigator';

	public function __construct(){
		$admin    = \Core\user()->checkAccess('g:admin');
		$this->canupload = $admin || \Core\user()->checkAccess('p:/tinymce/filebrowser/upload');
		$this->canaccess = $this->canupload || \Core\user()->checkAccess('p:/tinymce/filebrowser/access');

		switch(\ConfigHandler::Get('/tinymce/user-uploads/sandbox')){
			case 'user-sandboxed':
				$this->_basedir = 'public/tinymce/' . \Core\user()->get('id') . '/';
				break;
			case 'shared-user-sandbox':
				$this->_basedir = 'public/tinymce/';
				break;
			case 'completely-open':
				$this->_basedir = 'public/';
				break;
		}
	}

	public function setView(\View $view){
		$this->_view = $view;
	}

	public function setAccept($mode){
		switch(strtolower($mode)){
			case 'image':
			case 'images':
				$this->_accept = 'image/*';
				break;
			default:
				$this->_accept = '*';
				break;
		}
	}

	public function setMode($mode){
		switch(strtolower($mode)){
			case 'list':
				$this->_mode = 'list';
				break;
			default:
				$this->_mode = 'icon';
				break;
		}
	}

	public function setBaseURL($page){
		$this->_baseurl = $page;
	}

	public function cwd(){
		return $this->_cwd;
	}

	public function cd($dir){
		$dir = str_replace('..', '', $dir);

		if($dir == '.') $dir = '';

		// Make sure it ends with a trailing slash.
		// If it doesn't, then the upload will be in the directory directoryfilename.
		if(substr($dir, -1) != '/') $dir .= '/';

		// make sure it exists.
		$dh = \Core\Filestore\Factory::Directory($this->_basedir . $dir);
		if(!$dh->exists()){
			throw new \Exception('Cannot switch to directory ' . $dir . ', does not exist');
		}

		$this->_cwd = $dir;
	}

	public function render(){
		if(!$this->_view){
			throw new \Exception('No view provided, please set one first!');
		}

		if(!($this->canupload || $this->canaccess)){
			return \View::ERROR_ACCESSDENIED;
		}

		$resolved = \Core::ResolveLink($this->_baseurl);
		$resolvedwmode = $resolved . (strpos($resolved, '?') === false ? '?' : '&') . 'mode=' . $this->_mode;
		$otherviewlink = $resolved . (strpos($resolved, '?') === false ? '?' : '&') . 'dir=' . urlencode($this->_cwd);
		// Get a list of the view modes along with current directories and titles.
		$viewmodes = array(
			[
				'mode'  => 'icon',
				'title' => 'View as Icons',
				'icon'  => 'th-large',
				'link'  => $otherviewlink . '&mode=icon'
			],
			[
				'mode'  => 'list',
				'title' => 'View as List',
				'icon'  => 'th-list',
				'link'  => $otherviewlink . '&mode=list'
			],
		);


		// This will create a navigatable tree of directory listings for the user.
		//$tree = explode('/', substr($dirname, strlen($basedirname)));
		$tree = explode('/', $this->_cwd);
		$treestack = '';
		foreach($tree as $k => $v){
			if(!trim($v)){
				unset($tree[$k]);
			}
			else{
				$treestack .= '/' . $v;
				$tree[$k] = array(
					'name'  => $v,
					'stack' => $treestack,
					'href'  => $resolvedwmode . '&dir=' . urlencode($treestack)
				);
			}
		}

		$dir = \Core\Filestore\Factory::Directory($this->_basedir . $this->_cwd);

		// Allow automatic creation of the root directory.
		if($this->_cwd == '' && !$dir->exists()){
			$dir->mkdir();
		}

		if(!$dir->exists()){
			return \View::ERROR_NOTFOUND;
		}

		$dirlen = strlen($dir->getPath());
		$baselen = strlen(\Core\Filestore\get_public_path());
		$directories = array();
		$files = array();
		foreach($dir->ls() as $file){
			if($file instanceof \Core\Filestore\Directory){
				// Give me a count of children in that directory.  I need to do the logic custom here because I only want directories and images.
				$count = 0;
				foreach($file->ls() as $subfile){
					if($file instanceof \Core\Filestore\Directory){
						$count++;
					}
					elseif(
						($file instanceof \Core\Filestore\File) &&
						\Core\Filestore\check_file_mimetype($this->_accept, $file->getMimetype(), $file->getExtension()) == ''
					){
						$count++;
					}
				}

				$directories[$file->getBasename()] = array(
					'object'     => $file,
					'name'       => $file->getBasename(),
					'browsename' => substr($file->getPath(), $dirlen),
					'children'   => $count,
					'href'       => $resolvedwmode . '&dir=' . urlencode(substr($file->getPath(), $baselen))
				);
			}
			elseif($file instanceof \Core\Filestore\File){
				// I only want images
				if(\Core\Filestore\check_file_mimetype($this->_accept, $file->getMimetype(), $file->getExtension()) != '') continue;

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

		// If it's a nested directory, provide a link back to the parent.
		if($this->_cwd != ''){
			$uplink = $resolvedwmode . '&dir=' . urlencode(dirname(substr($dir->getPath(), $baselen)));
		}
		else{
			$uplink = null;
		}

		// Only certain people are allowed the rights to upload here.
		if($this->canupload){
			$uploadform = new \Form();
			//$uploadform->set('action', \Core::ResolveLink('/tinymcenavigator/upload'));
			$uploadform->addElement(
				'multifile',
				array(
					'basedir' => $this->_basedir . $this->_cwd,
					'title' => 'Upload Files',
					'name' => 'files',
					'accept' => $this->_accept
				)
			);
			//$uploadform->addElement('submit', array('value' => 'Upload Files'));
			$uploadform->clearFromSession();
		}
		else{
			$uploadform = false;
		}


		if($this->canupload){
			$this->_view->addControl(
				[
					'link' => '#',
					'title' => 'Create Directory',
					'icon' => 'folder-close',
					'class' => 'directory-create',
				]
			);
		}

		foreach($viewmodes as $viewmode){
			if($viewmode['mode'] == $this->_mode) continue;
			$this->_view->addControl($viewmode);
		}

		switch($this->_mode){
			case 'list':
				$this->_view->templatename = 'pages/tinymcenavigator/index/list.tpl';
				break;
			default:
				$this->_view->templatename = 'pages/tinymcenavigator/index/icons.tpl';
		}
		$this->_view->assign('directories', array_values($directories));
		$this->_view->assign('files', array_values($files));
		$this->_view->assign('location_tree', $tree);
		$this->_view->assign('location', $treestack);
		$this->_view->assign('uploadform', $uploadform);
		$this->_view->assign('baseurl', \Core::ResolveLink($this->_baseurl));
		$this->_view->assign('mode', $this->_mode);
		$this->_view->assign('uplink', $uplink);
		$this->_view->assign('canupload', $this->canupload);

		return \View::ERROR_NOERROR;
	}

	/**
	 * Make a directory in the current working directory.
	 *
	 * @param $directory
	 *
	 * @throws \Exception
	 */
	public function mkdir($directory) {
		if(!$this->canupload){
			throw new \Exception('Modify access denied!');
		}

		// Sanitize the filename
		if(
			strpos($directory, '..') !== false ||
			strpos($directory, '/') !== false
		){
			throw new \Exception('Invalid directory name');
		}

		$directory = trim($directory);

		$dir = \Core\Filestore\Factory::Directory($this->_basedir . $this->_cwd . '/' . $directory);
		if($dir->exists()){
			throw new \Exception('Directory ' . $directory . ' already exists!');
		}

		if(!$dir->mkdir()){
			throw new \Exception('Unable to create directory ' . $directory);
		}
	}

	public function rename($old, $new){
		if(!$this->canupload){
			throw new \Exception('Modify access denied!');
		}

		$old = trim($old);
		$old = trim($old, '/');
		$new = trim($new);
		$new = trim($new, '/');
		// Sanitize the filenames
		if(
			strpos($old, '..') !== false ||
			strpos($old, '/') !== false
		){
			throw new \Exception('Invalid directory name');
		}

		if(
			strpos($new, '..') !== false ||
			strpos($new, '/') !== false
		){
			throw new \Exception('Invalid directory name');
		}


		$dir = \Core\Filestore\Factory::Directory($this->_basedir . $this->_cwd);

		$obj = $dir->get($old);
		if(!$obj){
			throw new \Exception($old . ' does not exist!');
		}

		$obj->rename($new);
	}

	public function delete($old){
		if(!$this->canupload){
			throw new \Exception('Modify access denied!');
		}

		$old = trim($old);
		$old = trim($old, '/');
		// Sanitize the filenames
		if(
			strpos($old, '..') !== false ||
			strpos($old, '/') !== false
		){
			throw new \Exception('Invalid directory name');
		}

		$dir = \Core\Filestore\Factory::Directory($this->_basedir . $this->_cwd);

		$obj = $dir->get($old);
		if(!$obj){
			throw new \Exception($old . ' does not exist!');
		}

		$obj->delete();
	}
}