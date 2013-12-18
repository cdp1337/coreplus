<?php
/**
 * Provides some controllers for any of the system form elements that need it.
 *
 * These generally provide some ajax functionality or similar to the UA.
 *
 * @package
 * @since 2.4.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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

	/**
	 * A page load to save a given form in session only.
	 * This applies all user-supplied values from the UA, but does not call the overall save method.
	 */
	public function savetemporary(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;
		$view->record = false;

		// This is an ajax-only request.
		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}

		$formid = $request->getPost('___formid');
		if(!$formid){
			return View::ERROR_NOTFOUND;
		}

		// Lookup that form!
		if(!isset($_SESSION['FormData'][ $formid ])){
			return View::ERROR_NOTFOUND;
		}

		/** @var $form Form */
		$form = unserialize($_SESSION['FormData'][ $formid ]);

		if(!$form){
			return View::ERROR_NOTFOUND;
		}

		// Run though each element submitted and try to validate it.
		if (strtoupper($form->get('method')) == 'POST') $src =& $_POST;
		else $src =& $_GET;

		$form->loadFrom($src);

		$form->saveToSession();

		$view->jsondata = ['status' => '1', 'message' => 'Saved POST temporarily'];

		// Yup, that's it!
		// Saving the form back to the session will preserve those values.
	}

	public function pageinsertables_update(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;
		$view->record = false;

		// This is an ajax-only request.
		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}

		$formid = $request->getPost('___formid');
		if(!$formid){
			return View::ERROR_NOTFOUND;
		}

		// Lookup that form!
		if(!isset($_SESSION['FormData'][ $formid ])){
			return View::ERROR_NOTFOUND;
		}

		/** @var $form Form */
		$form = unserialize($_SESSION['FormData'][ $formid ]);

		if(!$form){
			return View::ERROR_NOTFOUND;
		}

		// Run though each element submitted and try to validate it.
		if (strtoupper($form->get('method')) == 'POST') $src =& $_POST;
		else $src =& $_GET;

		$form->loadFrom($src, true);

		// Now that the form has been loaded with the data, reinitialize the page's insertable elements.
		foreach($form->getModels() as $prefix => $model){
			if($model instanceof PageModel && $form->getElement($prefix . '[page_template]')){
				$pagetemplate = $form->getElement($prefix . '[page_template]');

				// Get all insertables currently present and remove them from the form.
				// (They will be added back shortly)
				foreach($form->getElements(true, false) as $el){
					$name = $el->get('name');
					if(strpos($name, $prefix . '[insertables]') === 0){
						$form->removeElement($name);
					}
				}

				// Now that the previous insertables are removed, update the value on the model and add the new insertables.
				$model->set('page_template', $pagetemplate->get('value'));
				$tpl = Core\Templates\Template::Factory($model->getTemplateName());
				if($tpl){
					// My counter for which element was added last... I need this because I have "addElementAfter"...
					// so if I just kept adding the stack after a single element, they'd be in reverse order.
					// ie: stack: [a, b, c] -> {ref_el}, c, b, a
					$lastelementadded = $pagetemplate;
					foreach($tpl->getInsertables() as $key => $dat){
						$type = $dat['type'];
						$dat['name'] = $prefix . '[insertables][' . $key . ']';

						// This insertable may already have content from the database... if so I want to pull that!
						$i = InsertableModel::Construct($model->get('baseurl'), $key);
						if ($i->get('value') !== null){
							$dat['value'] = $i->get('value');
						}

						$dat['class'] = 'insertable';

						$insertableelement = FormElement::Factory($type, $dat);
						$form->addElementAfter($insertableelement, $lastelementadded);
						$lastelementadded = $insertableelement;
					}
				}

				// Since there are new elements here, there may be old values that correspond to the new elements too.
				$form->loadFrom($src, true);

				// Don't forget to resave these form updates back to the session!
				$form->saveToSession();

				$view->jsondata = array(
					'status' => '1',
					'message' => 'Switched templatename successfully',
				);
				return;
			} // if($model instanceof PageModel && $form->getElement($prefix . '[page_template]'))
		} // foreach($form->getModels() as $prefix => $model)

		// Ummmm.....
		$view->jsondata = array(
			'status' => '0',
			'message' => 'No page found :/',
		);
	}

	public function pagemetas_autocompleteuser(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;
		$view->record = false;

		// This is an ajax-only request.
		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}

		// Does the user have access to search for users?
		if(!\Core\user()->checkAccess('p:/user/search/autocomplete')){
			return View::ERROR_ACCESSDENIED;
		}

		$term = $request->getParameter('term');
		$results = UserModel::Search($term);

		// I want to order them by relevancy.
		$sr = new Core\Search\SearchResults();
		$sr->addResults($results);
		$sr->sortResults();

		$filteredresults = array();
		foreach($sr->get() as $user){
			/** @var Core\Search\ModelResult $user */

			/** @var UserModel $model */
			$model = $user->_model;

			// This model will only be added to the form if it's active.
			if(!$model->get('active')){
				continue;
			}

			$filteredresults[] = array(
				'id'    => $model->get('id'),
				'label' => $model->getDisplayName(),
				'value' => $model->get('id'),
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
		$view->record = false;

		// This is an ajax-only request.
		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}

		$ds = new Core\Datamodel\Dataset();
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
			$results = UserModel::Search($term);
			foreach($results as $r){
				/** @var $r \Core\Search\ModelResult */

				/** @var UserModel $user */
				$user = $r->_model;
				$view->jsondata[] = array(
					'id' => 'u:' . $user->get('id'),
					'label' => $user->getDisplayName(),
					'value' => 'u:' . $user->get('id'),
				);
			}
		}
	}
}

