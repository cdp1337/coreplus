<?php
/**
 * Provides some controllers for any of the system form elements that need it.
 *
 * These generally provide some ajax functionality or similar to the UA.
 *
 * @package
 * @since 2.4.1
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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
		if(\Core\Session::Get('FormData/' . $formid) === null){
			return View::ERROR_NOTFOUND;
		}

		/** @var $form Form */
		$form = unserialize(\Core\Session::Get('FormData/' . $formid));

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

	/**
	 * @return null|int
	 */
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
		if(\Core\Session::Get('FormData/' . $formid) === null){
			return View::ERROR_NOTFOUND;
		}

		/** @var $form Form */
		$form = unserialize(\Core\Session::Get('FormData/' . $formid));

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
				// This block of logic is required because the template systems look at the last template set in the database.
				// Since what's in the databse isn't what we want here, we need to spoof it so the correct template is
				// used for retrieving form elements.
				$model->set('page_template', $pagetemplate->get('value'));

				// Set the last_template so that the traditional queries to getTemplate work without reverting back to the default template.
				$t = $model->getBaseTemplateName();

				// Allow the specific template to be overridden.
				if (($override = $model->get('page_template'))){
					$t = substr($t, 0, -4) . '/' . $override;
					$model->set('last_template', $t);
				}
				else{
					$model->set('last_template', null);
				}

				$tpl = Core\Templates\Template::Factory($model->getTemplateName());
				if($tpl){
					// My counter for which element was added last... I need this because I have "addElementAfter"...
					// so if I just kept adding the stack after a single element, they'd be in reverse order.
					// ie: stack: [a, b, c] -> {ref_el}, c, b, a
					$lastelementadded = $pagetemplate;
					$insertables = $tpl->getInsertables();
					foreach($insertables as $key => $dat){
						$type = $dat['type'];
						$dat['name'] = $prefix . '[insertables][' . $key . ']';

						// This insertable may already have content from the database... if so I want to pull that!
						$i = InsertableModel::Construct($model->get('baseurl'), $key);
						if ($i->get('value') !== null){
							$dat['value'] = $i->get('value');
						}

						$dat['class'] = 'insertable';

						$insertableelement = \Core\Forms\FormElement::Factory($type, $dat);
						$form->addElementAfter($insertableelement, $lastelementadded);
						$lastelementadded = $insertableelement;
					}
				}

				// Since there are new elements here, there may be old values that correspond to the new elements too.
				$form->loadFrom($src, true);

				// Don't forget to re-save these form updates back to the session!
				$form->persistent = true;
				$form->saveToSession();

				$view->jsondata = array(
					'status' => '1',
					'message' => 'Switched templatename successfully',
					'formid' => $form->get('uniqueid'),
				);
				return null;
			} // if($model instanceof PageModel && $form->getElement($prefix . '[page_template]'))
		} // foreach($form->getModels() as $prefix => $model)

		// Ummmm.....
		$view->jsondata = array(
			'status' => '0',
			'message' => 'No page found :/',
			'formid' => null,
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

		$includeInactive = ($request->getParameter('inactive') == '1');
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
			if(!($model->get('active') || $includeInactive)){
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

	/**
	 * Page to test the UI of form elements.
	 *
	 * This will generate a form with every registered form element.
	 */
	public function testui(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			// This test page is an admin-only utility.
			return View::ERROR_ACCESSDENIED;
		}

		$form = new \Core\Forms\Form();

		// What type of orientation do you want to see?
		$orientation = $request->getParameter('orientation');
		if(!$orientation){
			$orientation = 'horizontal';
		}
		$required = ($request->getParameter('required'));
		$error    = ($request->getParameter('error'));


		$form->set('orientation', $orientation);

		$mappings = \Core\Forms\Form::$Mappings;
		// Make them alphabetical.
		ksort($mappings);

		foreach($mappings as $k => $v){
			try{
				$atts = [
					'name' => $k,
					'title' => $v,
					'description' => 'This form element is a ' . $v . ', registered to the key ' . $k . '.',
				];

				if($required) $atts['required'] = true;

				// Some form elements have particular requirements.
				switch($v){
					case '\\Core\\Forms\\FileInput':
					case 'MultiFileInput':
						$atts['basedir'] = 'public/form-testui';
						break;
					case '\\Core\\Forms\\PagePageSelectInput':
						$atts['templatename'] = 'foo';
						break;
					case '\\Core\\Forms\\PageInsertables':
						$atts['baseurl'] = '/';
						break;
					case '\\Core\\Forms\\PageMeta':
						$atts['name'] = 'test';
						break;
					case '\\Core\\Forms\\CheckboxesInput':
					case '\\Core\\Forms\\RadioInput':
						$atts['options'] = ['key1' => 'Key 1', 'key2' => 'Key 2'];
						break;
				}
				$el = \Core\Forms\FormElement::Factory($k, $atts);

				if($error && $el instanceof \Core\Forms\FormElement){
					$el->setError('Something bad happened', false);
				}
				$form->addElement( $el );
			}
			catch(Exception $e){
				\Core\set_message('Form element ' . $v . ' failed to load due to ' . $e->getMessage(), 'error');
			}
		}

		$view->title = 'Test Form Element UI/UX';
		$view->assign('form', $form);
		$view->assign('orientation', $orientation);
		$view->assign('required', $required);
		$view->assign('error', $error);
	}
}

