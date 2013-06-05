<?php
/**
 * File for class TinyMCENavigatorController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130603.1641
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


/**
 * Provides a nautilus/explorer UI of the public files currently on the server.
 *
 * These are files that can be managed by super admins anyway.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for TinyMCENavigatorController
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
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class TinyMCENavigatorController extends  Controller_2_1{

	/**
	 * Main view for the navigator.
	 *
	 * Handle both list and thumbnail views.
	 *
	 * @return int
	 */
	public function index(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$navigator = new \TinyMCE\Navigator();
		$navigator->setView($view);
		if($request->getParameter('ajax')){
			$navigator->setBaseURL('/tinymcenavigator?ajax=1');
			$view->mastertemplate = 'blank.tpl';
		}
		else{
			$navigator->setBaseURL('/tinymcenavigator');
			$view->mastertemplate = 'admin';
		}

		try{
			$navigator->cd($request->getParameter('dir'));
			$navigator->setMode($request->getParameter('mode'));
		}
		catch(Exception $e){
			Core::SetMessage($e->getMessage(), 'error');
		}

		$view->mode = View::MODE_PAGEORAJAX;
		$view->title = 'Uploaded Files Navigator';
		$navigator->render();
		//var_dump($navigator, $navigator->render()); die();
	}

	/**
	 * Main view for the navigator.
	 *
	 * Handle both list and thumbnail views.
	 *
	 * @return int
	 */
	public function image(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$navigator = new \TinyMCE\Navigator();
		$navigator->setView($view);
		$navigator->setMode($request->getParameter('mode'));
		$navigator->setAccept('image');
		if($request->getParameter('ajax')){
			$navigator->setBaseURL('/tinymcenavigator/image?ajax=1');
			$view->mastertemplate = 'blank.tpl';
		}
		else{
			$navigator->setBaseURL('/tinymcenavigator/image');
			$view->mastertemplate = 'admin';
		}

		try{
			$navigator->cd($request->getParameter('dir'));
		}
		catch(Exception $e){
			Core::SetMessage($e->getMessage(), 'error');
		}

		$view->mode = View::MODE_PAGEORAJAX;
		$view->title = 'Images';
		$navigator->render();
		//var_dump($navigator, $navigator->render()); die();
	}


	/**
	 * Helper function for the directory mkdir command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function directory_mkdir(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		$navigator = new \TinyMCE\Navigator();
		$navigator->setView($view);

		if(!$request->isPost()){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid request, please ensure it is a POST.');
			return;
		}

		if(!isset($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a directory name');
			return;
		}

		if(!trim($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a directory name');
			return;
		}

		try{
			$navigator->cd($request->getPost('dir'));
			$navigator->mkdir($_POST['newdir']);
		}
		catch(Exception $e){
			$view->jsondata = ['status' => 0, 'message' => $e->getMessage()];
			return;
		}

		$view->jsondata = array('status' => 1, 'message' => 'Created directory successfully');
	}

	/**
	 * Helper function for the directory rename command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function directory_rename(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		$navigator = new \TinyMCE\Navigator();
		$navigator->setView($view);

		if(!$request->isPost()){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid request, please ensure it is a POST.');
			return;
		}

		if(!isset($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a directory name');
			return;
		}

		if(!isset($_POST['olddir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Previous directory is required!');
			return;
		}

		if(!trim($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a directory name');
			return;
		}

		try{
			$navigator->cd($request->getPost('dir'));
			$navigator->rename($_POST['olddir'], $_POST['newdir']);
		}
		catch(Exception $e){
			$view->jsondata = ['status' => 0, 'message' => $e->getMessage()];
			return;
		}

		$view->jsondata = array('status' => 1, 'message' => 'Renamed directory successfully');
	}

	/**
	 * Helper function for the directory delete command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function directory_delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		$navigator = new \TinyMCE\Navigator();
		$navigator->setView($view);

		if(!$request->isPost()){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid request, please ensure it is a POST.');
			return;
		}

		if(!isset($_POST['olddir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Previous directory is required!');
			return;
		}

		try{
			$navigator->cd($request->getPost('dir'));
			$navigator->delete($_POST['olddir']);
		}
		catch(Exception $e){
			$view->jsondata = ['status' => 0, 'message' => $e->getMessage()];
			return;
		}

		$view->jsondata = array('status' => 1, 'message' => 'Deleted directory successfully');
	}

	/**
	 * Helper function for the file rename command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function file_rename(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		$navigator = new \TinyMCE\Navigator();
		$navigator->setView($view);

		if(!$request->isPost()){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid request, please ensure it is a POST.');
			return;
		}

		if(!isset($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a file name');
			return;
		}

		if(!isset($_POST['olddir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Previous file is required!');
			return;
		}

		if(!trim($_POST['newdir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Please enter a file name');
			return;
		}

		try{
			$navigator->cd($request->getPost('dir'));
			$navigator->rename($_POST['olddir'], $_POST['newdir']);
		}
		catch(Exception $e){
			$view->jsondata = ['status' => 0, 'message' => $e->getMessage()];
			return;
		}

		$view->jsondata = array('status' => 1, 'message' => 'Renamed file successfully');
	}

	/**
	 * Helper function for the file delete command.
	 *
	 * Returns JSON data and is expected to be called via ajax.
	 */
	public function file_delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		$navigator = new \TinyMCE\Navigator();
		$navigator->setView($view);

		if(!$request->isPost()){
			$view->jsondata = array('status' => 0, 'message' => 'Invalid request, please ensure it is a POST.');
			return;
		}

		if(!isset($_POST['olddir'])){
			$view->jsondata = array('status' => 0, 'message' => 'Previous file is required!');
			return;
		}

		try{
			$navigator->cd($request->getPost('dir'));
			$navigator->delete($_POST['olddir']);
		}
		catch(Exception $e){
			$view->jsondata = ['status' => 0, 'message' => $e->getMessage()];
			return;
		}

		$view->jsondata = array('status' => 1, 'message' => 'Deleted file successfully');
	}
}