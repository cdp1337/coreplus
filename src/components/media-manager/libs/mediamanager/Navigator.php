<?php
/**
 * File for class Navigator definition in the coreplus project
 * 
 * @package MediaManager
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130603.1802
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

namespace MediaManager;
use Core\Filestore;


/**
 * The MediaManager/Navigator is intended to be a full management suite for your website.
 *
 * It can manage just /public/media, or all of /public.
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
 * @package MediaManager
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Navigator {

	public $canupload   = false;
	public $canaccess   = false;
	public $usecontrols = true;
	public $useuploader = true;

	/**
	 * @var \View
	 */
	protected $_view;
	protected $_basedir;
	protected $_accept = '*';
	protected $_mode = 'icon';
	protected $_cwd = '';
	protected $_baseurl = '/mediamanagernavigator';

	public function __construct(){
		$admin    = \Core\user()->checkAccess('g:admin');
		$this->canupload = $admin || \Core\user()->checkAccess('p:/mediamanager/upload');
		$this->canaccess = $this->canupload || \Core\user()->checkAccess('p:/mediamanager/browse');

		switch(\ConfigHandler::Get('/mediamanager/sandbox')){
			case 'user-sandboxed':
				$this->_basedir = 'public/media/' . \Core\user()->get('id') . '/';
				break;
			case 'shared-user-sandbox':
				$this->_basedir = 'public/media/';
				break;
			case 'completely-open':
				$this->_basedir = 'public/';
				break;
			default:
				$this->_basedir = 'public/media/';
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

	/**
	 * Get this navigator's current working directory, (after the basedir).
	 *
	 * @return string
	 */
	public function cwd(){
		return $this->_cwd;
	}

	/**
	 * Change directories into a nested directory, (after the basedir).
	 *
	 * @param $dir
	 *
	 * @throws \Exception
	 */
	public function cd($dir){
		$dir = str_replace('..', '', $dir);

		// "." is the shorthand for the root directory.
		if($dir == '.') $dir = '';

		// Make sure it ends with a trailing slash.
		// If it doesn't, then the upload will be in the directory directoryfilename.
		if(substr($dir, -1) != '/') $dir .= '/';

		// make sure it exists.
		$dh = Filestore\Factory::Directory($this->_basedir . $dir);
		if(!$dh->exists()){
			throw new \Exception('Cannot switch to directory ' . $dir . ', does not exist');
		}

		$this->_cwd = $dir;
	}

	/**
	 * Get the file in the current directory by its basename.
	 *
	 * @param $filename
	 * @return \Core\Filestore\File
	 *
	 * @throws \Exception
	 */
	public function getFile($filename){
		$dir  = Filestore\Factory::Directory($this->_basedir . $this->_cwd);
		if(!$dir->exists()){
			throw new \Exception('Cannot switch to directory ' . $this->_cwd . ', does not exist');
		}

		$file = $dir->get($filename);
		if(!$file){
			throw new \Exception('File ' . $filename . ' does not exist!');
		}
		elseif($file instanceof Filestore\File){
			return $file;
		}
		else{
			throw new \Exception('Requested file ' . $filename . ' is not a file!');
		}
	}

	/**
	 * Execute the navigator and return the exit code of the application.
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function render(){
		if(!$this->_view){
			throw new \Exception('No view provided, please set one first!');
		}

		if(!($this->canupload || $this->canaccess)){
			return \View::ERROR_ACCESSDENIED;
		}

		$resolved = \Core\resolve_link($this->_baseurl);
		// Tack on the system-defined uri-based options.
		$resolved .= (strpos($resolved, '?') === false ? '?' : '&') . 'controls=' . ($this->usecontrols ? '1' : '0') . '&uploader=' . ($this->useuploader ? '1' : '0');
		// This is meant to be called from most of the change directory methods, so it needs to drop that argument.
		$resolvedwmode = $resolved . '&mode=' . $this->_mode;

		// Get a list of the view modes along with current directories and titles.
		$viewmodes = array(
			[
				'mode'  => 'icon',
				'title' => 'View as Icons',
				'icon'  => 'th-large',
				'link'  => $resolved . '&dir=' . urlencode($this->_cwd) . '&mode=icon'
			],
			[
				'mode'  => 'list',
				'title' => 'View as List',
				'icon'  => 'th-list',
				'link'  => $resolved . '&dir=' . urlencode($this->_cwd) . '&mode=list'
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

		// The base directory, because it may not be in /public necessarily.
		$base = Filestore\Factory::Directory($this->_basedir);
		$dir  = Filestore\Factory::Directory($this->_basedir . $this->_cwd);

		// Allow automatic creation of the root directory.
		if($this->_cwd == '' && !$dir->exists()){
			$dir->mkdir();
		}

		if(!$dir->exists()){
			return \View::ERROR_NOTFOUND;
		}

		$dirlen = strlen($dir->getPath());
		$baselen = strlen($base->getPath());
		$directories = array();
		$files = array();
		foreach($dir->ls() as $file){
			if($file instanceof Filestore\Directory){
				// Give me a count of children in that directory.  I need to do the logic custom here because I only want directories and images.
				$count = 0;
				foreach($file->ls() as $subfile){
					if($file instanceof Filestore\Directory){
						$count++;
					}
					elseif(
						($file instanceof Filestore\File) &&
						Filestore\check_file_mimetype($this->_accept, $file->getMimetype(), $file->getExtension()) == ''
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
			elseif($file instanceof Filestore\File){
				// I only want images
				if(Filestore\check_file_mimetype($this->_accept, $file->getMimetype(), $file->getExtension()) != '') continue;

				$files[$file->getBaseFilename()] = array(
					'object'     => $file,
					'name'       => $file->getBaseFilename(),
					'browsename' => substr($file->getFilename(), $baselen),
					'selectname' => $file->getURL(),
					'corename'   => $file->getFilename(false),
				);
			}
		}

		// Sorting would be nice!
		ksort($directories, SORT_STRING | SORT_FLAG_CASE);
		ksort($files, SORT_STRING | SORT_FLAG_CASE);

		// If it's a nested directory, provide a link back to the parent.
		if($this->_cwd == ''){
			$uplink = null;
		}
		elseif($this->_cwd == '/'){
			$uplink = null;
		}
		else{
			$uplink = $resolvedwmode . '&dir=' . urlencode(dirname(substr($dir->getPath(), $baselen)));
		}

		// Only certain people are allowed the rights to upload here.
		if($this->canupload && $this->useuploader){
			$uploadform = new \Core\Forms\Form();
			//$uploadform->set('action', \Core\resolve_link('/mediamanagernavigator/upload'));
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


		if($this->canupload && $this->usecontrols){
			$this->_view->addControl(
				[
					'link' => '#',
					'title' => 'Create Directory',
					'icon' => 'folder-close',
					'class' => 'directory-create',
				]
			);
		}

		if($this->usecontrols){
			foreach($viewmodes as $viewmode){
				if($viewmode['mode'] == $this->_mode) continue;
				$this->_view->addControl($viewmode);
			}
		}

		switch($this->_mode){
			case 'list':
				$this->_view->templatename = 'pages/mediamanagernavigator/index/list.tpl';
				break;
			default:
				$this->_view->templatename = 'pages/mediamanagernavigator/index/icons.tpl';
		}
		$this->_view->assign('directories', array_values($directories));
		$this->_view->assign('files', array_values($files));
		$this->_view->assign('location_tree', $tree);
		$this->_view->assign('location', $treestack);
		$this->_view->assign('uploadform', $uploadform);
		$this->_view->assign('baseurl', $resolvedwmode);
		$this->_view->assign('mode', $this->_mode);
		$this->_view->assign('uplink', $uplink);
		$this->_view->assign('canupload', $this->canupload);
		$this->_view->assign('usecontrols', $this->usecontrols);

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

		$dir = Filestore\Factory::Directory($this->_basedir . $this->_cwd . '/' . $directory);
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


		$dir = Filestore\Factory::Directory($this->_basedir . $this->_cwd);

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

		$dir = Filestore\Factory::Directory($this->_basedir . $this->_cwd);

		$obj = $dir->get($old);
		if(!$obj){
			throw new \Exception($old . ' does not exist!');
		}

		$obj->delete();
	}
}