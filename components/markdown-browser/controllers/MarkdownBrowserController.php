<?php
/**
 * Main markdown browser controller.  This is responsible for administrative tasks and viewing tasks.
 *
 * @package MarkdownBrowser
 * @since 1.0
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

class MarkdownBrowserController extends Controller_2_1{
	/**
	 * The administrative listing page for markdown parents.
	 *
	 * This will not list each individual page, but instead the parent directories.
	 *
	 * @return int Return status
	 */
	public function index(){

		$view = $this->getView();

		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		// I'll need the list of top level directories in the markdown-browser directory.  These will be main groups.
		$directories = array();

		// Open the directory.
		$dh = Core::Directory('public/markdown-browser');
		foreach($dh->ls() as $f){
			if($f instanceof File_Backend) continue;

			$directories[] = $f->getBasename();
		}

		// The remaining arrays will be two; currently created pages and orphaned pages.
		// These will both be pulled from the database.
		$pages = array();
		$orphaned = array();
		$pfac = PageModel::Find('baseurl LIKE /markdownbrowser/view/%');
		foreach($pfac as $page){
			// Directory will be the last part after the url.
			$dir = substr($page->get('baseurl'), 22);
			if(!in_array($dir, $directories)){
				$orphaned[] = $dir;
			}
			else{
				$pages[] = $dir;
				unset($directories[array_search($dir, $directories)]);
			}
		}

		$view->templatename = '/pages/markdownbrowser/index.tpl';
		$view->title = 'Markdown Directory Listings';
		$view->assign('newdirectories', $directories);
		$view->assign('pages', $pages);
		$view->assign('orphaned', $orphaned);
	}

	/**
	 * Add or edit an existing directory.
	 *
	 * @return int
	 */
	public function update(){

		$view = $this->getView();

		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$dir    = $this->getPageRequest()->getParameter(0);
		$page   = new PageModel('/markdownbrowser/view/' . $dir);
		$exists = $page->exists();

		//$form = new Form();
		$form = Form::BuildFromModel($page);

		$form->set('callsmethod', 'MarkdownBrowserController::_SaveHandler');
		$form->set('directory', $dir);
		//$form->addElement('pagemeta', array('name' => 'page'));
		// Change the rewrite url and title to something useful if it's new.
		if(!$exists){
			$title = self::DirToName($dir);
			$url = ((PageModel::Count('baseurl = ' . $dir) > 0) ? '/markdownbrowser/view/' : '/') . $dir;
			$form->getElement('model[title]')->setValue($title);
			$form->getElement('model[rewriteurl]')->setValue($url);
		}

		// Tack on a submit button
		$form->addElement('submit', array('value' => ($exists ? 'Update' : 'Create')));

		$view->templatename = '/pages/markdownbrowser/update.tpl';
		//$view->addBreadcrumb('Markdown Directory Listings', '/markdownbrowser');
		$view->title = ($exists ? 'Update' : 'New') . ' Markdown Page Directory';
		$view->assignVariable('page', $page);
		$view->assignVariable('form', $form);
	}

	/**
	 * View a directory listing or individual markdown page.
	 *
	 * This page must have already been created as a hardset page in the database.
	 */
	public function view(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		// This view's access string will be based on the top-level parent.
		$top = PageModel::Find('baseurl = /markdownbrowser/view/' . $request->getParameter(0), 1);

		// Top-most parent needs to exist.
		if(!$top->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$this->setAccess($top->get('access'))){
			return View::ERROR_ACCESSDENIED;
		}

		// I need to build the path of directories from the top down to the current to
		// 1) build a breadcrumb back up and
		// 2) ensure that these directories actually exist.

		$dirset = 'public/markdown-browser';
		$path   = '/markdownbrowser/view';
		//$path   = $top->getResolvedURL();
		$atfile = false;
		$last   = null;
		$dir    = null;

		foreach($request->getParameters() as $k => $v){
			if(!is_numeric($k)) break;

			if($v{0} == '.'){
				return View::ERROR_NOTFOUND;
			}

			// Because I need to act on the *previous* item to ensure that the last one is the current page for breadcrumbs...
			if($last !== null && $k > 1){
				$view->addBreadcrumb($last['title'], $last['path']);
			}

			$dirset .= '/' . $v;
			$path   .= '/' . $v;
			$title   = ($k == 0) ? $top->get('title') : self::DirToName($v);
			$dir     = Core::Directory($dirset);
			if($dir->exists()){
				$last = array('title' => $title, 'path' => $path);
			}
			else{
				// hopefully it's the MD file itself and is at the end of the stream :p
				$atfile = true;
				$last = array('title' => $title, 'path' => $path);
				break;
			}
		}

		// No directory, the above loop must not have run!
		if($dir === null){
			return View::ERROR_NOTFOUND;
		}

		$view->title = $last['title'];

		if($atfile){
			$parent = Core::Directory(dirname($dirset));
			$filereq = strtolower($dirset); // The filename itself, to lowercase.
			$filereglen = strlen($filereq);
			$found = false;
			foreach($parent->ls() as $file){
				// Scan through each file in the parent listing.
				// If the filename matches the end of the parent's file, (case insensitive),
				// that must be the file!
				if(stripos($file->getFilename(), $filereq) == strlen($file->getFilename()) - $filereglen){
					$found = true;
					break;
				}
			}

			if(!$found){
				return View::ERROR_NOTFOUND;
			}

			$view->templatename = 'pages/markdownbrowser/view-file.tpl';
			//$file = Core::File($dirset);
			if(!$file->exists()){
				return View::ERROR_NOTFOUND;
			}

			$view->assign('contents', Markdown($file->getContents()));
		}
		else{
			$view->templatename = 'pages/markdownbrowser/view-listing.tpl';

			$directories = array();
			$files = array();

			foreach($dir->ls() as $f){
				// Because I need to make the title "pretty"...
				if($f instanceof File_Backend){
					$files[$f->getBaseFilename()] = array(
						'title' => self::DirToName($f->getBaseFilename()),
						'href' => $path . '/' . $f->getBaseFilename(false)
					);
				}
				else{
					$directories[$f->getBasename()] = array('title' => self::DirToName($f->getBasename()), 'href' => $path . '/' . $f->getBasename());
				}
			}
			ksort($directories);
			ksort($files);

			$view->assign('directories', $directories);
			$view->assign('files', $files);
		}

	}

	/**
	 * Save new and existing listings.
	 *
	 * @static
	 *
	 * @param Form $form
	 *
	 * @return mixed
	 */
	public static function _SaveHandler(Form $form){

		$model = $form->getModel();
		$model->set('baseurl', '/markdownbrowser/view/' . $form->get('directory'));
		$model->set('fuzzy', true);
		$model->save();

		// w00t
		return $model->getResolvedURL();
	}

	/**
	 * Simple function to return a directory name to a fancy human-readable name.
	 *
	 * @static
	 *
	 * @param $dir string
	 * @return string
	 */
	private static function DirToName($dir){
		// Since this works for both directories and files...
		if(preg_match('#\.md$#i', $dir)) $dir = substr($dir, 0, -3);

		return ucwords(preg_replace('#[^a-z]#', ' ', strtolower($dir)));
	}
}
