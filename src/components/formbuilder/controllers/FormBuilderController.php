<?php
/**
 * Class file for the controller FormBuilderController
 *
 * @package FormBuilder
 * @author Nicholas Hinsch <nicholas@eval.bz>
 */
class FormBuilderController extends Controller_2_1 {
	// @todo Add your views here
	// Each controller can have many views, each defined by a different method.
	// These methods should be regular public functions that DO NOT begin with an underscore (_).
	// Any method that begins with an underscore or is static will be assumed as an internal method
	// and cannot be called externally via a url.


	/**
	 * The frontend listing page that displays all blog articles that are published across the system.
	 */
	public function index(){
		$view     = $this->getView();
		$request  = $this->getPageRequest();
		$manager  = \Core\user()->checkAccess('p:/formbuilder/manage_all');


		// Get a list of all the custom forms on the system.  I'll get the page object from each one and see if the current user has access
		// to each one.  Then I'll have a list of ids that the user can view.
		$parents = array();
		$forms = FormBuilderModel::Find(null, null, null);
		foreach($forms as $f){
			/** @var BlogModel $blog */
			$page     = $f->getLink('Page');
			$editor   = \Core\user()->checkAccess($f->get('manage_forms_permission ')) || $manager;

			$parents[] = $f->get('baseurl');
		}

		// Is the user a manager, but no forms exist on the system?
		if($manager && !sizeof($parents)){
			\core\redirect('/admin/formbuilder/create');
		}

		$filters = new FilterForm();
		$filters->haspagination = true;
		$filters->setLimit(20);
		$filters->load($this->getPageRequest());

		$factory = new ModelFactory('PageModel');
		$factory->where('parenturl IN ' . implode(',', $parents));

		if($request->getParameter('q')){
			$query = $request->getParameter('q');
			$factory->where(\Core\Search\Helper::GetWhereClause($request->getParameter('q')));
		}
		else{
			$query = null;
		}

		$factory->order('created DESC');

		$filters->applyToFactory($factory);
		$forms = $factory->get();

		//var_dump($factory, $articles); die();

		$view->mode = View::MODE_PAGEORAJAX;
		$view->assign('articles', $forms);
		$view->assign('page', $page);
		$view->assign('filters', $filters);
		$view->assign('query', $query);

		if ($manager) {
			$view->addControl('Create New Custom Form', '/admin/formbuilder/create', 'add');
			$view->addControl('Manage Custom Forms', '/admin/formbuilder/edit', 'edit');
			$view->addControl('View Custom Forms', '/admin/formbuilder/index', 'view');
		}
	}

	public function create() {
		if (!$this->setAccess('p:/formbuilder/manage_all')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view = $this->getView();
		$formbuilder = new FormBuilderModel();
		$formbuilderfield = new FormBuilderFieldModel();
		$form = new Form();
		//$form->set('callsmethod', 'FormBuilderHelper::FormBuilderFormHandler');

		$form->addModel($formbuilder->getLink('Page'), 'page');
		$form->addModel($formbuilder, 'model');

		$form->addModel($formbuilderfield, 'fieldmodel');

		$form->switchElementType('fieldmodel[type]','select');
		$form->getElement('fieldmodel[type]')->setFromArray([
				'options' => [
					'text' => 'Text Field',
					'textarea' => 'Textarea',
					'email' => 'Email Address',
					'select' => 'Select',
					'multiselect' => 'Multi Select',
					'date' => 'Date',
					'time' => 'Time',
					'datetime' => 'Date/Time',
					'radio' => 'Radio Button',
					'state' => 'State Select',
					'country' => 'Country Select',

				],
				'name' => 'Field Type',
				'id' => 'fieldtype',
				'grouptype' => 'tabs',
				'group' => 'Form Elements',
			]);

		$form->addElement('submit', array('value' => 'Create'));

		//$view->addBreadcrumb('Blog Administration', '/blog/admin');
		$view->mastertemplate = 'admin';
		$view->templatename = 'pages/formbuilder/update.tpl';
		$view->title = 'Create Custom Form';
		$view->assignVariable('form', $form);


	}

	public function update() {
		if (!$this->setAccess('p:/formbuilder/manage_all')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view     = $this->getView();
		$request  = $this->getPageRequest();

		$formbuilder    = new FormBuilderModel($request->getParameter(0));
		if (!$formbuilder->exists()) {
			return View::ERROR_NOTFOUND;
		}

		$form = new Form();
		//$form->set('callsmethod', 'FormBuilderHelper::FormBuilderFormHandler');

		$form->addModel($formbuilder->getLink('Page'), 'page');
		$form->addModel($formbuilder, 'model');

		$form->addElement('submit', array('value' => 'Update'));

		// Some elements of the form need to be readonly.
		$form->getElement('model[type]')->set('disabled', true);

		$view->addBreadcrumb($$formbuilder->get('title'), $$formbuilder->get('rewriteurl'));
		$view->mastertemplate = 'admin';
		$view->title = 'Update Custom Form';
		$view->assignVariable('form', $form);

	}

	public function delete() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		$form = new FormBUilderModel($request->getParameter(0));
		if (!$form->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/formbuilder/manage_all');

		if (!$manager) {
			return View::ERROR_ACCESSDENIED;
		}

		if (!$request->isPost()) {
			return View::ERROR_BADREQUEST;
		}

		$form->delete();
		\core\go_back();
	}


}