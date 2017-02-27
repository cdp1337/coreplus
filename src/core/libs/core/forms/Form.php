<?php
/**
 * All core Form objects in the system
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@evalagency.com>
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

namespace Core\Forms;

/**
 * The main Form object.
 *
 * @package Core\Forms
 */
class Form extends FormGroup {

	/** @var string The original URL of the page this form was rendered on.  Used for security. */
	public $originalurl = '';

	/** @var string The referring page from this form.  Used for redirect purposes. */
	public $referrer = '';

	/**
	 * Standard mappings for 'text' to class of the FormElement.
	 * This can be extended, ie: wysiwyg or captcha.
	 *
	 * @var array
	 */
	public static $Mappings = array(
		'access'           => '\\Core\\Forms\\AccessStringInput',
		'button'           => '\\Core\\Forms\\ButtonInput',
		'checkbox'         => '\\Core\\Forms\\CheckboxInput',
		'checkboxes'       => '\\Core\\Forms\\CheckboxesInput',
		'date'             => '\\Core\\Forms\\DateInput',
		'datetime'         => '\\Core\\Forms\\DateTimeInput',
		'file'             => '\\Core\\Forms\\FileInput',
		'hidden'           => '\\Core\\Forms\\HiddenInput',
		'license'          => '\\Core\\Forms\\LicenseInput',
		//'pageinsertables'  => '\\Core\\Forms\\PageInsertables',
		'pagemeta'         => '\\Core\\Forms\\PageMeta',
		'pagemetas'        => '\\Core\\Forms\\PageMetasInput',
		'pagemetaauthor'   => '\\Core\\Forms\\PageMetaAuthorInput',
		'pagemetakeywords' => '\\Core\\Forms\\PageMetaKeywordsInput',
		'pageparentselect' => '\\Core\\Forms\\PageParentSelectInput',
		'pagerewriteurl'   => '\\Core\\Forms\\PageRewriteURLInput',
		'pagethemeselect'  => '\\Core\\Forms\\PageThemeSelectInput',
		'pagepageselect'   => '\\Core\\Forms\\PagePageSelectInput',
		'password'         => '\\Core\\Forms\\PasswordInput',
		'radio'            => '\\Core\\Forms\\RadioInput',
		'reset'            => '\\Core\\Forms\\ResetInput',
		'select'           => '\\Core\\Forms\\SelectInput',
		'state'            => '\\Core\\Forms\\StateInput',
		'submit'           => '\\Core\\Forms\\SubmitInput',
		'system'           => '\\Core\\Forms\\SystemInput',
		'text'             => '\\Core\\Forms\\TextInput',
		'textarea'         => '\\Core\\Forms\\TextareaInput',
		'time'             => '\\Core\\Forms\\TimeInput',
		'user'             => '\\Core\\Forms\\UserInput',
		'wysiwyg'          => '\\Core\\Forms\\TextareaInput',
	);

	public static $GroupMappings = array(
		'tabs'             => '\\Core\\Forms\\TabsGroup',
	);


	/**
	 * A cache of the actual models attached via addModel().
	 *
	 * @var array
	 */
	private $_models = array();


	/**
	 * Construct a new Form object
	 *
	 * @param array $atts Array of attribute to assign to this form off the bat.
	 */
	public function  __construct($atts = null) {

		if($atts === null){
			$atts = [];
		}
		// Some defaults
		if(!isset($atts['method'])) $atts['method'] = 'POST';
		if(!isset($atts['orientation'])) $atts['orientation'] = 'horizontal';

		parent::__construct($atts);

		$this->_validattributes = array('accept', 'accept-charset', 'action', 'enctype', 'id', 'method', 'name', 'target', 'style');

		// Will get set back to true on form submission for preserving the input values.
		$this->persistent = false;
	}

	public function getTemplateName() {
		return 'forms/form.tpl';
	}

	/**
	 * Generate a unique hash for this form and return it as a flattened string.
	 * @return string
	 */
	public function generateUniqueHash(){
		$hash = '';
		$set = false;

		// Tack on the destination method of this form.
		$hash .= $this->get('callsmethod') . ';';

		// Add in any/all model primary keys on this form.
		foreach($this->_models as $m => $model){
			/** @var \Model $model */
			$i = $model->GetIndexes();

			if(isset($i['primary'])){
				if(is_array($i['primary'])){
					foreach($i['primary'] as $k){
						$hash .= $m . '.' . $k . ':' . $model->get($k) . ';';
					}
				}
				else{
					$hash .= $m . '.' . $i['primary'] . ':' . $model->get( $i['primary'] ) . ';';
				}
			}
		}

		// And lastly any system inputs that may be present on the form.
		foreach ($this->getElements() as $el) {
			// Skip the ___formid element... this shouldn't affect the unique hash!
			if($el->get('name') == '___formid') continue;

			// System inputs require the value as well, since they're set by the controller; they're not
			// meant to be changed.
			if($el instanceof FormSystemInput){
				$set = true;
				$hash .= get_class($el) . ':' . $el->get('name') . ':' . json_encode($el->get('value')) . ';';
			}
			//else{
			//	$hash .= get_class($el) . ':' . $el->get('name') . ';';
			//}
		}

		if(!$set){
			// If there are no unique values set, then go back through and re-add the standard inputs.
			foreach ($this->getElements() as $el) {
				// Skip the ___formid element... this shouldn't affect the unique hash!
				if($el->get('name') == '___formid') continue;

				// System inputs require the value as well, since they're set by the controller; they're not
				// meant to be changed.
				if(!($el instanceof FormSystemInput)){
					$hash .= get_class($el) . ':' . $el->get('name') . ';';
				}
			}
		}

		// Hash it!
		$hash = md5($hash);

		return $hash;
	}

	/**
	 * Render this form and all inside elements to valid HTML.
	 *
	 * This will also save the form to the session data for post-submission validation.
	 *  (if called with null or "foot")
	 *
	 * @param mixed $part "body|head|foot| or null
	 *        Render just a specific part of the form.  Useful for advanced usage.
	 *        null: Render all of the form and its element.
	 *        "head": Render just the beginning of the form, including the <form> opening tag.
	 *        "body": Render just the body of the form, specifically the elements.
	 *        "foot": Render just the end of the form, including the </form> closing tag.
	 *
	 * @return string (valid HTML)
	 */
	public function  render($part = null) {

		// Check and see if there are any elements in this form that require a fileupload.
		foreach ($this->getElements() as $e) {
			if ($e->requiresupload) {
				$this->set('enctype', 'multipart/form-data');
				break;
			}
		}

		// Will be used to know if the errors in elements should be removed prior to rendering.
		$ignoreerrors = false;

		// Slip in the formid tracker to remember this submission.
		if (($part === null || $part == 'body') && $this->get('callsmethod')) {
			/*$e               = new FormHiddenInput(array('name'  => '___formid',
			                                             'value' => $this->get('uniqueid')));
			$this->_elements = array_merge(array($e), $this->_elements);
			*/

			/*
			// I need to ensure a repeatable but unique id for this form.
			// Essentially when this form is submitted, I need to be able to know that it's the same form upon re-rendering.
			if (!$this->get('uniqueid')) {
				$hash = $this->generateUniqueHash();
				$this->set('uniqueid', $hash);
				$this->getElementByName('___formid')->set('value', $hash);
			}
			*/

			// Was this form already submitted, (and thus saved in the session?
			// If so, render that form instead!  This way the values get transported seamlessly.

			// I need the hash at present, regardless if all elements have been rendered to the screen or not.
			$hash = ($this->get('uniqueid') ? $this->get('uniqueid') : $this->generateUniqueHash());

			if (($savedform = \Core\Session::Get('FormData/' . $hash)) !== null) {
				if (($savedform = unserialize($savedform))) {

					/** @var Form $savedform */
					// If this form is not set as persistent, then don't restore the values!
					if($savedform->persistent){
						foreach($this->_elements as $k => $element){
							/** @var FormElement $element */
							if($element->persistent){
								$this->_elements[$k] = $savedform->_elements[$k];
							}
						}
					}
				}
				else {
					$ignoreerrors = true;
				}
			}
			else {
				$ignoreerrors = true;
			}
		}

		if(($part == null || $part == 'foot') && $this->get('callsmethod')){
			// I need to ensure a repeatable but unique id for this form.
			// Essentially when this form is submitted, I need to be able to know that it's the same form upon re-rendering.
			if (!$this->get('uniqueid')) {
				$hash = $this->generateUniqueHash();
				$this->set('uniqueid', $hash);
			}
		}

		if ($ignoreerrors) {
			foreach ($this->getElements(true) as $el) {
				$el->setError(false);
			}
		}

		$tpl = \Core\Templates\Template::Factory('forms/form.tpl');
		$tpl->assign('group', $this);
		if ($part === null || $part == 'body') {
			$els = '';
			// Fill in the elements
			foreach ($this->_elements as $e) {
				$els .= $e->render();
			}
			$tpl->assign('elements', $els);
		}

		switch ($part) {
			case null:
				$out = $tpl->fetch('forms/form.tpl');
				break;
			case 'head':
				$out = $tpl->fetch('forms/form.head.tpl');
				break;
			case 'body':
				$out = $tpl->fetch('forms/form.body.tpl');
				break;
			case 'foot':
				$out = $tpl->fetch('forms/form.foot.tpl');
				break;
			default:
				if(($el = $this->getElement($part)) !== false){
					$out = $el->render();
				}
		}

		// Save it
		$this->referrer = \Core\page_request()->referrer;
		$this->originalurl = CUR_CALL;
		$this->persistent = false;
		if (($part === null || $part == 'foot') && $this->get('callsmethod')) {
			$this->saveToSession();
		}

		return $out;
	}

	/**
	 * Get a group by its name/title.
	 * Will create the group if it does not exist.
	 *
	 * @param string $name Name of the group to find/create
	 * @param string $type Type of group, used in conjunction with the GroupMappings array
	 * @return FormGroup
	 */
	public function getGroup($name, $type = 'default'){
		$element = $this->getElement($name);
		if(!$element){
			// Determine the type type.
			if(isset(self::$GroupMappings[$type])) $class = self::$GroupMappings[$type];
			else $class = '\\Core\\Forms\\FormGroup'; // Default.

			$ref = new \ReflectionClass($class);
			$element = $ref->newInstance(['name' => $name, 'title' => $name]);
			$this->addElement($element);
		}

		return $element;
	}

	/**
	 * Get the associated model for this form, if there is one.
	 * This model will also be populated automatically with all the data submitted.
	 *
	 * @param string $prefix The prefix name to lookup the model with.
	 *
	 * @return \Model
	 */
	public function getModel($prefix = 'model') {

		// A model needs to be defined first of all...
		if(!isset($this->_models[$prefix])){
			return null;
		}
		/** @var $model \Model */
		$model = $this->_models[$prefix];

		//$m = $this->get('___' . $prefix . 'name');
		//if (!$m) return null; // A model needs to be defined first of all...

		//$model = new $m();

		//if (!$model instanceof Model) return null; // It needs to be a model... :/

		// Page models have special functionality.
		// This is because they are almost always embedded in forms, so they have their own getModel logic,
		// allowing them to be singled out and that model extracted along side the main form's model.
		//if($model instanceof PageModel){
		//	// Find the page and return its model.
		//	foreach($this->getElements(false, false) as $el){
		//		if($el instanceof FormPageMeta){
		//			return $el->getModel();
		//		}
		//	}
		//}


		// Set the PK's...
		//if (is_array($this->get('___' . $prefix . 'pks'))) {
		//	foreach ($this->get('___' . $prefix . 'pks') as $k => $v) {
		//		$model->set($k, $v);
		//	}
		//
		// It should now be loadable.
		//	$model->load();
		//}

		$model->setFromForm($this, $prefix);

		return $model;
	}

	/**
	 * Get the unmodified models that are attached to this form.
	 * @return array
	 */
	public function getModels(){
		return $this->_models;
	}

	/**
	 * Load this form's values from the provided array, usually GET or POST.
	 * This is really an internal function that should not be called externally.
	 *
	 * @param array   $src
	 * @param boolean $quiet Set to true to squelch errors.
	 */
	public function loadFrom($src, $quiet = false) {
		$els = $this->getElements(true, false);
		foreach ($els as $e) {
			/** @var $e FormElement */
			// Be sure to clear any errors from the previous page load....
			$e->clearError();

			if($e->get('disabled')){
				// Readonly elements cannot get written from the UA.
				continue;
			}

			$e->set('value', $e->lookupValueFrom($src));
			if ($e->hasError() && !$quiet){
				\Core\set_message($e->getError(), 'error');
			}
		}
	}

	/**
	 * Add a model's rendered elements to this form.
	 *
	 * All models must have a common prefix, generally this is "model", but if multiple models are on one form,
	 *  then different prefixes can be used.
	 * 
	 * If $overrideElements is used, that array is used as the source of elements instead of
	 * querying the Model for.  This is useful if you need to change the default behaviour
	 * of the Form elements in the Controller prior to rendering.
	 *
	 * @param \Model $model            Model to populate elements from
	 * @param string $prefix           Prefix to create elements as
	 * @param array  $overrideElements Array of Elements to override the Model's defined attributes.
	 */
	public function addModel(\Model $model, $prefix = 'model', $overrideElements = null){

		// Is this model already attached?
		if(isset($this->_models[$prefix])){
			return;
		}

		$this->_models[$prefix] = $model;
		
		if($overrideElements !== null){
			$elements = $overrideElements;
		}
		else{
			// The model can handle giving an array of form elements.
			$elements = $model->getAsFormArray();
		}
		
		foreach($elements as $k => $el){
			// Update the name as it will need to be prefixed with this model's prefix.
			if($prefix){
				$name = $el->get('name');
				if(preg_match('/^[a-zA-Z_]*\[/', $name)){
					// Name already contains blah[foo]
					// Translate that to $prefix[blah][foo].
					$name = $prefix . '[' . preg_replace('/^([a-zA-Z_]*)\[/', '$1][', $name);
				}
				else{
					$name = $prefix . '[' . $name . ']';
				}
				$el->set('name', $name);
			}
			
			// I need to give the model a chance to act on this new element too.
			// Sometimes models may have a few special things to update on the element.
			// $model->setFromForm($this, $prefix);
			$model->setToFormElement($k, $el);
			
			$this->addElement($el);
		}

		// Anything else?
		$model->addToFormPost($this, $prefix);
	}

	/**
	 * Add a given element to this form, (or group in this form).
	 * If the element as the "group" property, it will automatically be added to that respective group.
	 *
	 * @param       $element
	 * @param array $atts
	 */
	public function addElement($element, $atts = []){
		// Group support! :)
		if(isset($atts['group'])){
			$grouptype = isset($atts['grouptype']) ? $atts['grouptype'] : 'default';

			$this->getGroup( $atts['group'], $grouptype )->addElement($element, $atts);
		}
		elseif($element instanceof FormElement && $element->get('group')){
			$grouptype = $element->get('grouptype') ? $element->get('grouptype') : 'default';

			$this->getGroup( $element->get('group'), $grouptype )->addElement($element, $atts);
		}
		else{
			parent::addElement($element, $atts);
		}
	}

	/**
	 * Switch an element type from one to another.
	 * This is useful for doing some fine tuning on a pre-generated form, ie
	 *  a "string" field in the Model should be interperuted as an image upload.
	 *
	 * @param string $elementname The name of the element to switch
	 * @param string $newtype The standard name of the new element type
	 *
	 * @return boolean Return true on success, false on failure.
	 */
	public function switchElementType($elementname, $newtype) {
		$el = $this->getElement($elementname);
		if (!$el) return false;

		// Default.
		if (!isset(self::$Mappings[$newtype])) $newtype = 'text';

		$cls = self::$Mappings[$newtype];

		// If it's already the newtype, no change required.
		if (get_class($el) == $cls) return false;

		$atts = $el->getAsArray();

		// Don't need this one
		unset($atts['__class']);
		$newel = new $cls();
		$newel->setFromArray($atts);
		//var_dump($el, $atts, $newel, $newel->getInputAttributes());
		$this->switchElement($el, $newel);
		return true;
	}

	/**
	 * Internal method to save a serialized version of this object
	 *     into the database so it can be loaded upon submitting.
	 *
	 * This is now public as of 2.4.1, but don't call it, seriously, leave it alone.  It doesn't want to talk to you.  EVAR!
	 *
	 * @return void
	 */
	public function saveToSession() {

		if (!$this->get('callsmethod')) return; // Don't save anything if there's no method to call.


		$this->set('expires', (int)\Core\Date\DateTime::NowGMT('U') + 1800); // 30 minutes

		\Core\Session::Set('FormData/' . $this->get('uniqueid'), serialize($this));
	}

	public function clearFromSession(){
		// If the unique hash has already been set, use that.
		// otherwise, generate it from the set elements.
		$hash = $this->get('uniqueid') ? $this->get('uniqueid') : $this->generateUniqueHash();

		\Core\Session::UnsetKey('FormData/' . $hash);
	}


	/**
	 * Function that is fired off on page load.
	 * This checks if a form was submitted and that form was present in the SESSION.
	 *
	 * @return null
	 */
	public static function CheckSavedSessionData() {
		// This needs to ignore the /form/savetemporary.ajax page!
		// This is a custom page that's meant to intercept all POST submissions.
		if(preg_match('#^/form/(.*)\.ajax$#', REL_REQUEST_PATH)) return;

		// There has to be data in the session.
		$forms = \Core\Session::Get('FormData/*');

		$formid = (isset($_REQUEST['___formid'])) ? $_REQUEST['___formid'] : false;
		$form   = false;

		foreach ($forms as $k => $v) {
			// If the object isn't a valid object after unserializing...
			if (!($el = unserialize($v))) {
				\Core\Session::UnsetKey('FormData/' . $k);
				continue;
			}

			// Check the expires time
			if ($el->get('expires') <= \Core\Date\DateTime::NowGMT('U')) {
				\Core\Session::UnsetKey('FormData/' . $k);
				continue;
			}

			if ($k == $formid) {
				// Remember this for after all the checks have finished.
				$form = $el;
			}
		}

		// No form found... simple enough
		if (!$form) return;

		// Otherwise
		/** @var $form Form */

		// Ensure the submission types match up.
		if (strtoupper($form->get('method')) != $_SERVER['REQUEST_METHOD']) {
			\Core\set_message('t:MESSAGE_ERROR_FORM_SUBMISSION_TYPE_DOES_NOT_MATCH');
			return;
		}

		// Ensure the REFERRER and original URL match up.
		if($_SERVER['HTTP_REFERER'] != $form->originalurl){
			// @todo This is reported to be causing issues with production sites.
			//       If found true, this check may need to be removed / refactored.
			//\Core\set_message('Form submission referrer does not match, please try your submission again.', 'error');
			\SystemLogModel::LogInfoEvent(
				'Form Referrer Mismatch',
				'Form referrer does not match!  Submitted: [' . $_SERVER['HTTP_REFERER'] . '] Expected: [' . $form->originalurl . ']'
			);
			//return;
		}

		// Run though each element submitted and try to validate it.
		if (strtoupper($form->get('method')) == 'POST') $src =& $_POST;
		else $src =& $_GET;

		$form->loadFrom($src);

		// Try to load the form from that form.  That will call all of the model's validation logic
		// and will throw exceptions if it doesn't.
		try{
			$form->getModel();

			// Still good?
			if (!$form->hasError()){
				$status = call_user_func($form->get('callsmethod'), $form);
			}
			else{
				$status = false;
			}
		}
		catch(\ModelValidationException $e){
			\Core\set_message($e->getMessage(), 'error');
			$status = false;
		}
		catch(\GeneralValidationException $e){
			\Core\set_message($e->getMessage(), 'error');
			$status = false;
		}
		catch(\Exception $e){
			if(DEVELOPMENT_MODE){
				// Developers get the full message
				\Core\set_message($e->getMessage(), 'error');
			}
			else{
				// While users of production-enabled sites get a friendlier message.
				\Core\set_message('t:MESSAGE_ERROR_FORM_SUBMISSION_UNHANDLED_EXCEPTION');
			}
			\Core\ErrorManagement\exception_handler($e);
			$status = false;
		}

		// The form was submitted.  Set its persistent flag to true so that whatever may be listening for it can retrieve the user's values.
		$form->persistent = true;

		// Regardless, bundle this form back into the session so the controller can use it if needed.
		\Core\Session::Set('FormData/' . $formid, serialize($form));

		// Fail statuses.
		if ($status === false) return;
		if ($status === null) return;

		// Guess it's not false and not null... must be good then.

		// @todo Handle an internal save procedure for "special" groups such as pageinsertables and what not.

		// Cleanup
		\Core\Session::UnsetKey('FormData/' . $formid);


		if ($status === 'die'){
			// If it's set to die, simply exit the script without outputting anything.
			exit;
		}
		elseif($status === 'back'){
			if($form->referrer && $form->referrer != REL_REQUEST_PATH){
				// Go back to the original form's referrer.
				\Core\redirect($form->referrer);
			}
			else{
				// Use Core to guess which page to redirect back to, (not as reliable).
				\Core\go_back();
			}
		}
		elseif ($status === true){
			// If the return code is boolean true, it's a reload.
			\Core\reload();
		}
		elseif($status === REL_REQUEST_PATH || $status === CUR_CALL){
			// If the page returned the same page as the current url, force a reload, (as redirect will ignore it)
			\Core\reload();
		}
		else{
			// Anything else gets sent to the redirect system.
			\core\redirect($status);
		}
	}

	/**
	 * Scan through a standard Model object and populate elements with the correct fields and information.
	 *
	 * @param \Model $model
	 *
	 * @return Form
	 */
	public static function BuildFromModel(\Model $model) {
		$f = new Form();
		$f->addModel($model);
		return $f;
	}
}

