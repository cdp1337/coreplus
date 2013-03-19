<?php
/**
 * Provides some controllers for any of the system form elements that need it.
 *
 * These generally provide some ajax functionality or similar to the UA.
 *
 * @package
 * @since 2.4.1
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

class FormController extends Controller_2_1 {
	public function pageinsertables_update(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		// This is an ajax-only request.
		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}

		// The form object is located in $_SESSION['FormData'].

		if($request->getPost('formid')){
			// Lookup that form!
			if(!isset($_SESSION['FormData'][ $request->getPost('formid') ])){
				return View::ERROR_NOTFOUND;
			}

			/** @var $form Form */
			$form = unserialize($_SESSION['FormData'][ $request->getPost('formid') ]);

			if(!$form){
				return View::ERROR_NOTFOUND;
			}

			/** @var $element FormPageInsertables */
			$element = $form->getElement( $request->getPost('elementname') );

			$element->setTemplateName( $request->getPost('templatename') );

			//var_dump($element); die();

			// Whee, don't forget to resave these form updates back to the session!
			$form->saveToSession();

			// And return something useful :)
			$view->jsondata = array(
				'status' => '1',
				'message' => 'Switched templatename successfully',
				'html' => $element->render()
			);
			return;
		}
		var_dump('0.ó', $_POST, $_SESSION); die();
	}

	public function pagemetas_autocompleteuser(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		// This is an ajax-only request.
		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}

		// Does the user have access to search for users?
		if(!\Core\user()->checkAccess('p:/user/search/autocomplete')){
			return View::ERROR_ACCESSDENIED;
		}

		$term = $request->getParameter('term');
		$results = User::Search($term);

		$filteredresults = array();
		foreach($results as $user){
			/** @var $user User_Backend */
			$filteredresults[] = array(
				'id' => $user->get('id'),
				'label' => $user->getDisplayName(),
				'value' => $user->get('id'),
			);
		}

		// The json data will contain the following keys for each element:
		// id, label, value

		$view->jsondata = $filteredresults;
	}

	public function pagemetas_autocompletekeyword(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;
		$term = $request->getParameter('term');

		// This is an ajax-only request.
		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}

		$ds = new Dataset();
		$ds->table('page_meta');
		$ds->uniquerecords = true;
		$ds->select('meta_value', 'meta_value_title');
		$ds->where('meta_key = keyword');
		$ds->where('meta_value_title LIKE ' . $term . '%');

		// Just in case there are a huge number of records...
		$stream = new DatasetStream($ds);
		$view->jsondata = array();
		while(($record = $stream->getRecord())){
			$view->jsondata[] = array(
				'id' => $record['meta_value'],
				'label' => $record['meta_value_title'],
				'value' => $record['meta_value'],
			);
		}

		// Does the user have access to search for users?
		// if so include that search here to!  This is for the subject matter tag, or "This x is about person y!"
		if(\Core\user()->checkAccess('p:/user/search/autocomplete')){
			$results = User::Search($term);
			foreach($results as $user){
				/** @var $user User_Backend */
				$view->jsondata[] = array(
					'id' => 'u:' . $user->get('id'),
					'label' => $user->getDisplayName(),
					'value' => 'u:' . $user->get('id'),
				);
			}
		}
	}
}

