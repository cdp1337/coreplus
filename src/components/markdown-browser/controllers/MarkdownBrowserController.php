<?php
/**
 * Main markdown browser controller.  This is responsible for administrative tasks and viewing tasks.
 *
 * @package MarkdownBrowser
 * @since 1.0
 * @author Charlie Powell <charlie@evalagency.com>
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
	
	public function admin(){
		$view = $this->getView();

		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$dir = \ConfigHandler::Get('/markdownbrowser/basedir');
		$markdownFiles = [];
		
		if(!$dir){
			\Core\set_message('t:MESSAGE_ERROR_MARKDOWNBROWSER_NO_CONFIGURED_DIRECTORY');
		}
		else{
			// Make sure it's readable!
			$dir = \Core\Filestore\Factory::Directory($dir);
			$dirbase = $dir->getPath();
			$dirlen = strlen($dirbase);
			if(!$dir->exists()){
				\Core\set_message('t:MESSAGE_ERROR_MARKDOWNBROWSER_DIRECTORY_DOES_NOT_EXIST');
			}
			else{
				$files = $dir->ls('md', true);
				foreach($files as $file){
					/** @var \Core\Filestore\File $file */
					$fileBase = substr($file->getFilename(), $dirlen);
					
					if(strpos($fileBase, 'index.md') !== false){
						$fileRel  = substr($fileBase, 0, -8);
					}
					else{
						$fileRel  = substr($fileBase, 0, -3);
					}
					
					$warning = '';
					if(preg_match('/[A-Z]/', $fileRel) !== 0){
						$warning = t('STRING_MARKDOWNBROWSER_WARNING_FILE_HAS_CAPITALS');
					}
					elseif(strpos($fileRel, ' ')){
						$warning = t('STRING_MARKDOWNBROWSER_WARNING_FILE_HAS_SPACES');
					}
					
					$markdownFiles[] = [
						'filename' => $fileBase,
						'view_url' => '/markdownbrowser/view/' . $fileRel,
						'edit_url' => '/markdownbrowser/update/' . $fileRel,
					    'page'     => PageModel::Construct('/markdownbrowser/view/' . $fileRel),
					    'warning'  => $warning,
					];
				}
			}
		}
		
		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'AdminController::_ConfigSubmit');
		$form->addElement(ConfigHandler::GetConfig('/markdownbrowser/basedir')->asFormElement());
		$form->addElement(ConfigHandler::GetConfig('/markdownbrowser/source')->asFormElement());
		$form->addElement(ConfigHandler::GetConfig('/markdownbrowser/autoregister')->asFormElement());
		$form->addElement('submit', ['value' => t('STRING_SAVE') ]);
		
		
		
		$view->title = 't:STRING_MARKDOWNBROWSER_ADMIN';
		$view->assign('form', $form);
		$view->assign('files', $markdownFiles);
	}

	/**
	 * Add or edit an existing directory.
	 *
	 * @return int
	 */
	public function update(){

		$view    = $this->getView();
		$request = $this->getPageRequest();

		if(!$this->setAccess('p:/content/manage_all')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$info = $this->_resolveInfoFromURL();
		
		if($info['status'] !== 200){
			return $info['status'];
		}
		
		/** @var \Core\Filestore\File $file */
		$file = $info['file'];
		/** @var PageModel $page */
		$page = $info['page'];

		$contents = $file->getContents();

		// Convert these contents from markdown to HTML.
		$processor = new \Core\MarkdownProcessor();
		$html = $processor->transform($contents);
		
		// Pre-populate this page with information from the rendered markdown document.
		// If this page exists, then it'll be updated and kept in sync.
		// Else, it'll still be set with what's in the document and kept in sync.
		$page->set('title', $processor->getMeta('title'));
		$page->set('body', $html);
		if(!$page->exists()){
			$page->set('baseurl', '/markdownbrowser/view' . $info['url']);
			$page->set('rewriteurl', '/markdownbrowser/view' . $info['url']);
			$page->set('editurl', '/markdownbrowser/update' . $info['url']);
			$page->set('component', 'markdown-browser');
			$page->set('selectable', 1);
			$page->set('published', $file->getMTime());
			$page->set('updated', $file->getMTime());
			$page->set('created', $file->getMTime());
		}
		
		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'MarkdownBrowserController::_SaveHandler');
		$form->addModel($page, 'page');
		
		// Many of these elements are readonly!
		$form->getElement('page[title]')->set('readonly', true);
		$form->removeElement('page[indexable]');
		$form->removeElement('page[metas][title]');
		$form->removeElement('page[metas][image]');
		$form->removeElement('page[metas][author]');
		$form->removeElement('page[metas][keywords]');
		$form->removeElement('page[metas][description]');
		$form->removeElement('page[published_status]');
		$form->removeElement('page[published]');
		$form->removeElement('page[published_expires]');

		// Tack on a submit button
		$form->addElement('submit', ['value' => ($page->exists() ? t('STRING_UPDATE') : t('STRING_REGISTER_PAGE'))] );
		
		// Give some useful instructions on why everything on this page is disabled!
		\Core\set_message('t:MESSAGE_TUTORIAL_MARKDOWNBROWSER_REGISTER_UPDATE_PAGE_NOTICE');

		$view->templatename = '/pages/markdownbrowser/update.tpl';
		//$view->addBreadcrumb('Markdown Directory Listings', '/markdownbrowser');
		$view->title = ($page->exists() ? t('STRING_UPDATE') : t('STRING_REGISTER_PAGE')) . ' ' . $info['relative_file'];
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
		
		// I need to build the path of directories from the top down to the current to
		// 1) build a breadcrumb back up and
		// 2) ensure that these directories actually exist.
		
		$info = $this->_resolveInfoFromURL();
		
		if($info['status'] !== 200){
			return $info['status'];
		}
		
		/** @var \Core\Filestore\File $file */
		$file = $info['file'];
		$enableSource = ConfigHandler::Get('/markdownbrowser/source');

		$path         = \ConfigHandler::Get('/markdownbrowser/basedir');
		$topPath      = \Core\Filestore\Factory::Directory($path);
		$topLen       = strlen($topPath->getPath());
		
		$parentPath = $file->getDirectoryName();
		$contents = $file->getContents();
		
		if($enableSource && $info['source']){
			// The user requested the source.
			$view->contenttype = 'text/x-markdown';
			$view->mode = View::MODE_NOOUTPUT;
			$view->render();
			
			echo $contents;
			return;
		}
		
		// Convert these contents from markdown to HTML.
		$helper = new MarkdownBrowserUrlHelper();
		$helper->basedir = $parentPath;
		$processor = new \Core\MarkdownProcessor();
		$processor->urlCallback = [$helper, 'parseURL'];
		$html = $processor->transform($contents);

		
		if(\Core\user()->checkAccess('p:/content/manage_all')){
			$view->addControl(t('STRING_UPDATE'), '/markdownbrowser/update' . $info['url'], 'edit');
		}
		if($enableSource){
			// Include a link to the original markdown.
			$view->addControl(t('STRING_MARKDOWNBROWSER_VIEW_SOURCE'), '/markdownbrowser/view' . $info['relative_file']);
		}
		
		$breadcrumbs = $this->_resolveBreadcrumbsFromURL();
		foreach($breadcrumbs as $bs){
			$view->addBreadcrumb($bs['title'], $bs['rewriteurl']);
		}
		
		$view->title = $processor->getMeta('title');
		$view->updated = $file->getMTime();
		if(($desc = $processor->getMeta('description'))){
			$view->addMetaName('description', $desc);
		}
		if(($keywords = $processor->getMeta('keywords'))){
			if(is_array($keywords)){
				$view->addMetaName('keywords', implode(', ', $keywords) );
			}
			else{
				$view->addMetaName('keywords', $keywords );
			}
		}
		if(($authors = $processor->getMeta('authors'))){
			if(is_array($authors)){
				foreach($authors as $a){
					$view->addMetaName('author', $a);
				}
			}
			else{
				$view->addMetaName('author', $authors );
			}
		}
		
		// New integration!
		// If Codemirror is available and there are &lt;code class="syntax-something"&gt; tags present,
		// try to include that "mode" for codemirror.
		if(
			Core::IsComponentAvailable('codemirror') &&
			preg_match('/<code[^>]*class="[^"]*syntax-[a-z]*[^"]*"[^>]*>/', $html)
		){
			$js = <<<EOD
<script>
$(function(){
	$('code.syntax-%SYNTAX%').each(function(){
		var jQthis          = $(this),
			jQparent        = jQthis.parent(),
			jQSiblingPrev   = jQparent.prev(),
			siblingPrevType = jQSiblingPrev[0].nodeName;
		
		// If this pre has a previous .pre-header, then add some CodeMirror classes to that.
		if(
			jQSiblingPrev.hasClass('pre-heading') &&
			(siblingPrevType == 'H3' || siblingPrevType == 'H4' || siblingPrevType == 'H5' || siblingPrevType == 'H6')
		){
			jQSiblingPrev.addClass('CodeMirror-heading');
		}
		
		// Prepend the type of this preformatted code.
		jQparent.append('<div class="CodeMirror-mode">%ALIAS%</div>');
		
		// This pre needs to know it's a CodeMirror pre.
		jQparent.addClass('CodeMirror-pre');
		
		// Lastly, initialize the code.
		new CodeMirror(jQparent[0], {
			value: jQthis.text().trim(),
			mode: '%ALIAS%',
			lineNumbers: true,
			readOnly: true
		});
		jQthis.hide();
	});
});
</script>
EOD;
			preg_match_all('/<code[^>]*class="[^"]*syntax-([a-z]*)[^"]*"[^>]*>/', $html, $matches);
			foreach($matches[1] as $lang){
				$alias = CodeMirror\Utils::IncludeMode($lang);
				$view->addScript(
					str_replace(
						['%SYNTAX%', '%ALIAS%'],
						[$lang, $alias],
						$js
					),
					'foot'
				);
			}
		}
		

		$view->templatename = 'pages/markdownbrowser/view-file.tpl';
		$view->assign('contents', $html);
	}

	/**
	 * Helper method for downloading an embedded file from a markdown file.
	 */
	public function download(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		// I need to build the path of directories from the top down to the current to
		// 1) build a breadcrumb back up and
		// 2) ensure that these directories actually exist.

		$info = $this->_resolveInfoFromURL();

		if($info['status'] !== 200){
			return $info['status'];
		}
		
		/** @var \Core\Filestore\File $file */
		$file = $info['file'];
		$file->sendToUserAgent(true);
		return;
	}

	/**
	 * Helper method for viewing an embedded image from a markdown file.
	 * 
	 * This expects the image to be available from within the registered markdown directory!
	 */
	public function img(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		// I need to build the path of directories from the top down to the current to
		// 1) build a breadcrumb back up and
		// 2) ensure that these directories actually exist.

		$path         = \ConfigHandler::Get('/markdownbrowser/basedir');
		$relPath      = '/markdownbrowser/view';

		foreach($request->getParameters() as $k => $v){
			if(!is_numeric($k)) break;

			if($v{0} == '.'){
				// Hidden directories and parent directories are invalid calls here!
				return View::ERROR_NOTFOUND;
			}

			$path    .= '/' . urldecode($v);
			$relPath .= '/' . $v;
		}

		// No directory, the above loop must not have run!
		if($path === null){
			return View::ERROR_NOTFOUND;
		}

		// Is the file requested a directory?
		// If so, redirect that to the index file, (if set).
		// And 404 if none set.
		$file = \Core\Filestore\Factory::File($path . '.' . $request->ext);
		if(!$file->exists()) {
			return View::ERROR_NOTFOUND;
		}
		
		// Only use this view to display images!
		if(!$file->isImage()){
			return View::ERROR_NOTFOUND;
		}
		$file->sendToUserAgent(false);
		return;
	}

	/**
	 * Resolve some useful information from the page parameters in relation to the requested markdown file.
	 * @return int
	 */
	private function _resolveInfoFromURL(){
		$request = $this->getPageRequest();
		
		$topPath       = \ConfigHandler::Get('/markdownbrowser/basedir');
		$topDirectory  = \Core\Filestore\Factory::Directory($topPath);
		// Resolve topPath to something reliable, just in case the user did something strange when entering it in.
		$topPath       = $topDirectory->getPath();
		$requestedFile = '';
		// The return data
		$return        = [
			'url'           => '',
			'status'        => View::ERROR_NOERROR,
		    'source'        => ($request->ext == 'md'),
		    'file'          => null,
		    'relative_file' => '',
		    'page'          => '',
		    'top_path'      => $topPath,
		    'top_directory' => $topDirectory,
		];
		
		foreach($request->getParameters() as $k => $v){
			if(!is_numeric($k)){
				// Stop processing of the parameters once a named GET parameter is hit.
				// These always mark the end of the URL path.
				break;
			}
			
			$v = urldecode($v);
			
			if($v{0} == '.'){
				// Hidden directories and parent directories are invalid calls here!
				$return['status'] = View::ERROR_NOTFOUND;
				return $return;
			}

			$requestedFile .= '/' . $v;
		}
		
		// Append the file extension if one was requested.
		if($request->ext == '' || $request->ext == 'html'){
			$return['url'] = $requestedFile;
			$file = \Core\Filestore\Factory::File($topPath . $requestedFile . '.md');
		}
		else{
			$return['url'] = $requestedFile . '.' . $request->ext;
			$file = \Core\Filestore\Factory::File($topPath . $requestedFile . '.' . $request->ext);
		}
		
		// Is the file requested a directory?
		// If so, redirect that to the index file, (if set).
		// And 404 if none set.
		if($file->exists()) {
			$return['file'] = $file;
			$return['relative_file'] = $requestedFile . '.md';
		}
		else{
			$directory = \Core\Filestore\Factory::Directory($topPath . $requestedFile);
			if(!$directory->exists()){
				$return['status'] = View::ERROR_NOTFOUND;
				return $return;
			}

			// The file needs to be index.md.
			// If not present...
			$file = \Core\Filestore\Factory::File($topPath . $requestedFile . '/index.md');
			if($file->exists()){
				$return['file'] = $file;
				$return['relative_file'] = $requestedFile . '/index.md';
			}
			else{
				$return['status'] = View::ERROR_NOTFOUND;
				return $return;
			}
		}
		
		// Lastly, ensure that the page is populated with the corresponding page model, (just in case).
		// This is used in the breadcrumbs and rewriteURL systems as those two fields are user-editable via the site.
		$return['page'] = PageModel::Construct('/markdownbrowser/view' . $return['url']);
		
		return $return;
	}

	/**
	 * Get the breadcrumbs for the given page.
	 * 
	 * @return array
	 */
	private function _resolveBreadcrumbsFromURL(){
		// Generate a stack of breadcrumbs for the page.
		// This will only utilize 'index.md' files, so ensure that those are present if you need to use this feature.
		// They will initially be ordered in reverse order, (top-down), so they'll be inverted before finalizing.
		
		$info = $this->_resolveInfoFromURL();
		
		// Easiest check, has it been set from the page object?
		/** @var PageModel $page */
		$page = $info['page'];
		if($page->exists() && $page->get('parenturl')){
			return $page->getParentTree();
		}
		
		/** @var \Core\Filestore\File $file */
		$file = $info['file'];
		
		$breadcrumbs = [];
		// If the page isn't already registered... start with the root page of the site.
		// it only makes sense that the site's top-level page is the default root breadcrumb.
		$root = PageModel::Find('rewriteurl = /', 1);
		if($root){
			$breadcrumbs[] = [
				'title' => $root->get('title'),
			    'rewriteurl'  => '/',
			];
		}
		
		// Now, disect the URL for the parts to look for index.md files!
		$parts = explode('/', $info['relative_file']);
		$checkDir = $info['top_path'];
		$relDir   = '';
		foreach($parts as $k => $p){
			// Top directory!?
			if($k > 0){
				// Allow the top directory to be checked for the presence of an index.md file.
				// (This doesn't play well with the appending of path + '/', so they're skipped here so as to not
				// produce view//blah/dir strings.)
				
				if($p == '' || $p{0} == '.'){
					continue;
				}
				$checkDir .= $p . '/';
				$relDir   .= '/' . $p;
			}
			
			$checkFile = \Core\Filestore\Factory::File($checkDir . 'index.md');
			if($checkFile->exists() && $checkFile->getFilename() != $file->getFilename()){
				// This file exists and is not the currently viewed file, (don't want to have double breadcrumbs).
				$checkUrl = '/markdownbrowser/view' . $relDir;

				// Convert these contents from markdown to HTML.
				$helper = new MarkdownBrowserUrlHelper();
				$helper->basedir = $checkFile->getDirectoryName();
				$checkProcessor = new \Core\MarkdownProcessor();
				$checkProcessor->urlCallback = [$helper, 'parseURL'];
				$checkProcessor->transform($checkFile->getContents());
				$breadcrumbs[] = [
					'title' => $checkProcessor->getMeta('title'),
					'rewriteurl' => $checkUrl,
				];
			}
		}
		
		return $breadcrumbs;
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
	public static function _SaveHandler(\Core\Forms\Form $form){

		$model = $form->getModel('page');
		$exists = $model->exists();
		$model->save();

		if($exists){
			\Core\set_message('t:MESSAGE_SUCCESS_UPDATED_MARKDOWNBROWSER_PAGE');
		}
		else{
			\Core\set_message('t:MESSAGE_SUCCESS_REGISTERED_MARKDOWNBROWSER_PAGE');
		}
		
		
		// w00t
		return $model->getResolvedURL();
	}

	/**
	 * Hook to check for any new files in the system and register pages (or deregister them as necessary).
	 * 
	 * Only runs if the config option is enabled to do so.
	 */
	public static function _AutoRegisterFiles(){
		if(!\ConfigHandler::Get('/markdownbrowser/autoregister')){
			echo 'Skipping autoregistration of markdown files, configuration option for this feature is disabled.' . "\n";
			return true;
		}
		
		$dir = \ConfigHandler::Get('/markdownbrowser/basedir');
		$markdownFiles = [];

		if(!$dir){
			echo 'Skipping autoregistration of markdown files, no markdown directory configured.' . "\n";
			return true;
		}
		
		// Make sure it's readable!
		$dir = \Core\Filestore\Factory::Directory($dir);
		$dirbase = $dir->getPath();
		$dirlen = strlen($dirbase);
		if(!$dir->exists()){
			echo 'Skipping autoregistration of markdown files, ' . $dir->getPath() . ' does not exist.' . "\n";
			return true;
		}
		
		$all = [];
		
		$files = $dir->ls('md', true);
		foreach($files as $file){
			/** @var \Core\Filestore\File $file */
			$fileBase = substr($file->getFilename(), $dirlen);

			if(strpos($fileBase, 'index.md') !== false){
				$fileRel  = substr($fileBase, 0, -8);
			}
			else{
				$fileRel  = substr($fileBase, 0, -3);
			}

			if(preg_match('/[A-Z]/', $fileRel) !== 0){
				$warning = t('STRING_MARKDOWNBROWSER_WARNING_FILE_HAS_CAPITALS');
			}
			elseif(strpos($fileRel, ' ')){
				$warning = t('STRING_MARKDOWNBROWSER_WARNING_FILE_HAS_SPACES');
			}
			else{
				$warning = '';
			}

			if($warning == ''){
				$url = '/markdownbrowser/view/' . $fileRel;

				$all[$url] = [
					'file' => $file,
					'page' => null,
				];	
			}
			else{
				echo $warning . ' - ' . $fileRel . "\n";
			}
		}
		
		
		// Now that the files are loaded into memory, load any page that may already exist.
		// This will be used to ignore entries that already have a page, to create ones without,
		// and to remove pages that no longer have a corresponding file.
		$pages = PageModel::Find(['baseurl LIKE /markdownbrowser/view%']);
		foreach($pages as $p){
			$url = $p->get('baseurl');
			
			if(!isset($all[$url])){
				$all[$url] = [
					'file' => null,
				    'page' => null,
				];
			}

			$all[$url]['page'] = $p;
		}
		
		// Now $all contains everything I need to process on! :)
		foreach($all as $url => &$dat){
			/** @var PageModel|null $page */
			$page = $dat['page'];
			/** @var \Core\Filestore\File|null $file */
			$file = $dat['file'];
			
			if($page && !$file){
				// There is a page but no file, DELETE!
				$page->delete();
				echo 'Deleted page for non-existent file: ' . $url . "\n";
			}
			elseif(!$page && $file){
				// There is a file but no page, create.
				$contents = $file->getContents();

				// Convert these contents from markdown to HTML.
				$processor = new \Core\MarkdownProcessor();
				$html = $processor->transform($contents);

				// Pre-populate this page with information from the rendered markdown document.
				// If this page exists, then it'll be updated and kept in sync.
				// Else, it'll still be set with what's in the document and kept in sync.
				$page = PageModel::Construct($url);
				$page->set('title', $processor->getMeta('title'));
				$page->set('body', $html);
				$page->set('baseurl', $url);
				$page->set('rewriteurl', $url);
				$page->set('editurl', str_replace('/markdownbrowser/view', '/markdownbrowser/update', $url));
				$page->set('component', 'markdown-browser');
				$page->set('selectable', 1);
				$page->set('published', $file->getMTime());
				$page->set('updated', $file->getMTime());
				$page->set('created', $file->getMTime());
				
				$page->save();
				echo 'Created page for new file: ' . $url . "\n";
			}
		}
		
		return true;
	}
}

class MarkdownBrowserUrlHelper{
	public $basedir;
	
	public function parseURL($url){
		$path         = \ConfigHandler::Get('/markdownbrowser/basedir');
		$dir          = \Core\Filestore\Factory::Directory($path);
		$dirbase      = $dir->getPath();
		$dirlen       = strlen($dirbase);
		$file         = \Core\filestore\Factory::File($this->basedir . '/' . $url);
		
		if($file->exists()){
			// Is this a markdown file or image?
			// If it's a markdown file, trim the '.md' off the end and give the relative path to the controller.
			if(strpos($url, '.md') !== false){
				return \Core\resolve_link('/markdownbrowser/view/' . substr($file->getFilename(), $dirlen, -3));
			}
			elseif($file->isImage()){
				return \Core\resolve_link('/markdownbrowser/img/' . substr($file->getFilename(), $dirlen));
			}
			else{
				return \Core\resolve_link('/markdownbrowser/download/' . substr($file->getFilename(), $dirlen));
			}
		}
		else{
			// Hmm....?
			return '';
		}
	}
}