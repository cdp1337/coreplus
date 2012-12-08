<?php
/**
 * All core Form objects in the system
 *
 * @package Core Plus\Core
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

class FormGroup {
	protected $_elements;

	protected $_attributes;

	protected $_validattributes = array();

	/**
	 * Boolean if this form element requires a file upload.
	 * Only "file" type elements should require this.
	 *
	 * @var boolean
	 */
	public $requiresupload = false;

	public function __construct($atts = null) {
		$this->_attributes = array();
		$this->_elements   = array();

		if ($atts) $this->setFromArray($atts);
	}

	public function set($key, $value) {
		$this->_attributes[strtolower($key)] = $value;
	}

	public function get($key) {
		$key = strtolower($key);
		return (isset($this->_attributes[$key])) ? $this->_attributes[$key] : null;
	}

	public function setFromArray($array) {
		foreach ($array as $k => $v) {
			$this->set($k, $v);
		}
	}

	public function hasError() {
		foreach ($this->_elements as $e) {
			if ($e->hasError()) return true;
		}

		return false;
	}

	public function getErrors() {
		$err = array();
		foreach ($this->_elements as $e) {
			if ($e instanceof FormGroup) $err = array_merge($err, $e->getErrors());
			elseif ($e->hasError()) $err[] = $e->getError();
		}
		return $err;
	}

	public function addElement($element, $atts = null) {
		// Since this allows for just plain names to be submitted, translate
		// them to the form object to be rendered.

		if ($element instanceof FormElement || is_a($element, 'FormElement')) {
			// w00t, already in the right format!
			if ($atts) $element->setFromArray($atts);
			$this->_elements[] = $element;
		}
		elseif ($element instanceof FormGroup) {
			// w00t, already in the right format!
			if ($atts) $element->setFromArray($atts);
			$this->_elements[] = $element;
		}
		else {
			if (!isset(Form::$Mappings[$element])) $element = 'text'; // Default.

			$this->_elements[] = new Form::$Mappings[$element]($atts);
		}
	}

	public function switchElement(FormElement $oldelement, FormElement $newelement) {
		foreach ($this->_elements as $k => $el) {
			// A match found?  Replace it!
			if ($el == $oldelement) {
				$this->_elements[$k] = $newelement;
				return true;
			}

			// If the element was another group, tell that group to scan too!
			if ($el instanceof FormGroup) {
				// Scan this object too!
				if ($el->switchElement($oldelement, $newelement)) return true;
			}
		}

		// No replacement?...
		return false;
	}

	/**
	 * Remove an element from the form by name.
	 * Useful for automatically generated forms and workin backwards instead of forward, (sometimes you only
	 * want to remove one or two fields instead of creating twenty).
	 *
	 * @param strign $name The name of the element to remove.
	 */
	public function removeElement($name){
		foreach ($this->_elements as $k => $el) {
			// A match found?  Replace it!
			if($el->get('name') == $name){
				unset($this->_elements[$k]);
				return true;
			}

			// If the element was another group, tell that group to scan too!
			if ($el instanceof FormGroup) {
				// Scan this object too!
				if ($el->removeElement($name)) return true;
			}
		}

		return false;
	}

	public function getTemplateName() {
		return 'forms/groups/default.tpl';
	}

	public function render() {
		$out = '';
		foreach ($this->_elements as $e) {
			$out .= $e->render();
		}

		$file = $this->getTemplateName();

		// Groups may not have a template... if so just render the children directly.
		if (!$file) return $out;

		$tpl = new Template();
		$tpl->assign('group', $this);
		$tpl->assign('elements', $out);
		return $tpl->fetch($file);
	}

	/**
	 * Template helper function
	 * gets the css class of the element.
	 * @return string
	 */
	public function getClass() {
		$c = $this->get('class');
		$r = $this->get('required');
		$e = $this->hasError();

		return $c . (($r) ? ' formrequired' : '') . (($e) ? ' formerror' : '');
	}

	/**
	 * Template helper function
	 * gets the input attributes as a string
	 * @return string
	 */
	public function getGroupAttributes() {
		$out = '';
		foreach ($this->_validattributes as $k) {
			if (($v = $this->get($k))) $out .= " $k=\"" . str_replace('"', '\\"', $v) . "\"";
		}
		return $out;
	}

	/**
	 * Get all elements in this group.
	 *
	 * @param boolean $recursively Recurse into subgroups.
	 * @param boolean $includegroups Include those subgroups (if recursive is enabled)
	 *
	 * @return array
	 */
	public function getElements($recursively = true, $includegroups = false) {
		$els = array();
		foreach ($this->_elements as $e) {
			// Tack on this element, regardless of what it is.
			//$els[] = $e;

			// Only include a group if recusively is set to false or includegroups is set to true.
			if (
				$e instanceof FormElement ||
				($e instanceof FormGroup && ($includegroups || !$recursively))
			) {
				$els[] = $e;
			}

			// In addition, if it is a group, delve into its children.
			if ($recursively && $e instanceof FormGroup) $els = array_merge($els, $e->getElements($recursively));
		}
		return $els;
	}

	/**
	 * Lookup and return an element based on its name.
	 *
	 * Shortcut of getElementByName()
	 *
	 * @param string $name The name of the element to lookup.
	 *
	 * @return FormElement
	 */
	public function getElement($name) {
		return $this->getElementByName($name);
	}

	/**
	 * Lookup and return an element based on its name.
	 *
	 * @param string $name The name of the element to lookup.
	 *
	 * @return FormElement
	 */
	public function getElementByName($name) {
		$els = $this->getElements(true, true);

		foreach ($els as $el) {
			if ($el->get('name') == $name) return $el;
		}

		return false;
	}
}

class FormElement {
	/**
	 * Array of attributes for this form element object.
	 * Should be in key/value pair.
	 *
	 * @var array
	 */
	protected $_attributes = array();

	protected $_error;

	/**
	 * Array of attributes to automatically return when getInputAttributes() is called.
	 *
	 * @var array
	 */
	protected $_validattributes = array();

	/**
	 * Boolean if this form element requires a file upload.
	 * Only "file" type elements should require this.
	 *
	 * @var boolean
	 */
	public $requiresupload = false;

	/**
	 * An optional validation check for this element.
	 * This can be multiple things, such as:
	 *
	 * "/blah/" - Evaluated with preg_match.
	 * "#blah#" - Also evaluated with preg_match.
	 * "MyFoo::Blah" - Evaluated with call_user_func.
	 *
	 * @var string
	 */
	public $validation = null;

	/**
	 * An optional message to post if the validation check fails.
	 *
	 * @var string
	 */
	public $validationmessage = null;

	public function __construct($atts = null) {

		if ($atts) $this->setFromArray($atts);
	}

	public function set($key, $value) {
		$key = strtolower($key);

		switch ($key) {
			case 'value': // Drop into special logic.
				$this->setValue($value);
				break;
			case 'label': // This is an alias for title.
				$this->_attributes['title'] = $value;
			case 'options':
				// This will require a little bit more attention, as if only the title
				// is given, use that for the value as well.
				if (!is_array($value)) {
					$this->_attributes[$key] = $value;
				}
				elseif(\Core\is_numeric_array($value)) {
					$o = array();
					foreach ($value as $v) {
						$o[$v] = $v;
					}
					$this->_attributes[$key] = $o;
				}
				else{
					// It's an associative or other array, the keys are important!
					$this->_attributes[$key] = $value;
				}
				break;
			case 'autocomplete':
				if(!$value){
					$this->_attributes[$key] = 'off';
				}
				else{
					$this->_attributes[$key] = 'on';
				}
				break;
			default:
				$this->_attributes[$key] = $value;
				break;
		}
	}

	public function get($key) {
		$key = strtolower($key);

		switch ($key) {
			case 'label': // Special case, returns either title or name, whichever is set.
				if (!empty($this->_attributes['title'])) return $this->_attributes['title'];
				else return $this->get('name');
				break;
			case 'id': // ID is also a special case, it casn use the name if not defined otherwise.
				return $this->getID();
				break;
			default:
				return (isset($this->_attributes[$key])) ? $this->_attributes[$key] : null;
		}
	}

	/**
	 * Get all attributes of this form element as a flat array.
	 * @return array
	 */
	public function getAsArray() {
		$ret            = array();
		$ret['__class'] = get_class($this);
		foreach ($this->_attributes as $k => $v) {
			$ret[$k] = (isset($this->_attributes[$k])) ? $this->_attributes[$k] : null;
		}
		return $ret;
	}

	public function setFromArray($array) {
		foreach ($array as $k => $v) {
			$this->set($k, $v);
		}
	}


	/**
	 * This set explicitly handles the value, and has the extended logic required
	 *  for error checking and validation.
	 *
	 * @param mixed $value The value to set
	 * @return boolean
	 */
	public function setValue($value) {
		if ($this->get('required') && !$value) {
			$this->_error = $this->get('label') . ' is required.';
			return false;
		}

		// If there's a value, pass it through the validation check, (if available).
		if ($value && $this->validation) {
			$vmesg = $this->validationmessage ? $this->validationmessage : $this->get('label') . ' does not validate correctly, please double check it.';
			$v     = $this->validation;

			// @todo Add support for a variety of validation logics maybe???

			// Method-based validation.
			if (strpos($v, '::') !== false && ($out = call_user_func($v, $value)) !== true) {
				// If a string was returned from the validation logic, set the error to that string.
				if ($out !== false) $vmesg = $out;
				$this->_error = $vmesg;
				return false;
			}
			// regex-based validation.  These don't have any return strings so they're easier.
			elseif (
				($v{0} == '/' && !preg_match($v, $value)) ||
				($v{0} == '#' && !preg_match($v, $value))
			) {
				if (DEVELOPMENT_MODE) $vmesg .= ' validation used: ' . $v;
				$this->_error = $vmesg;
				return false;
			}
		}

		$this->_attributes['value'] = $value;
		return true;
	}

	public function hasError() {
		return ($this->_error);
	}

	public function getError() {
		return $this->_error;
	}

	public function setError($err, $displayMessage = true) {
		$this->_error = $err;
		if ($err && $displayMessage) Core::SetMessage($err, 'error');
	}

	public function clearError() {
		$this->setError(false);
	}

	public function getTemplateName() {
		return 'forms/elements/' . strtolower(get_class($this)) . '.tpl';
	}

	public function render() {

		// If multiple is set, but the name does not have a [] at the end.... add it.
		if ($this->get('multiple') && !preg_match('/.*\[.*\]/', $this->get('name'))) $this->_attributes['name'] .= '[]';

		$file = $this->getTemplateName();

		$tpl = new Template();

		$tpl->assign('element', $this);

		return $tpl->fetch($file);
	}

	/**
	 * Template helper function
	 * gets the css class of the element.
	 * @return string
	 */
	public function getClass() {
		$c = $this->get('class');
		$r = $this->get('required');
		$e = $this->hasError();

		return $c . (($r) ? ' formrequired' : '') . (($e) ? ' formerror' : '');
	}

	/**
	 * Get the ID for this element, will either return the user-set ID, or an automatically generated one.
	 *
	 * @return string
	 */
	public function getID(){
		// If the ID is already set, return that.
		if (!empty($this->_attributes['id'])){
			return $this->_attributes['id'];
		}
		// I need to generate a javascript and UA friendly version from the name.
		else{
			$n = $this->get('name');
			$c = strtolower(get_class($this));
			// Prepend the form type to the name.
			$id = $c . '-' . $n;
			// Remove empty parantheses, (there shouldn't be any)
			$id = str_replace('[]', '', $id);
			// And replace brackets with dashes appropriatetly
			$id = preg_replace('/\[([^\]]*)\]/', '-$1', $id);

			return $id;
		}
	}

	/**
	 * Template helper function
	 * gets the input attributes as a string
	 * @return string
	 */
	public function getInputAttributes() {
		$out = '';
		foreach ($this->_validattributes as $k) {
			if ($k == 'required'){
				// 'Required' is skipped if it's false.
				if(!$this->get($k)){
					continue;
				}
				else{
					$out .= ' required="required"';
				}
			}
			elseif($k == 'checked'){
				// 'checked' is skipped if false also.
				if(!$this->get($k)){
					continue;
				}
				else{
					$out .= ' checked="checked"';
				}
			}
			elseif (($v = $this->get($k)) !== null){
				$out .= " $k=\"" . str_replace('"', '&quot;', $v) . "\"";
			}
		}
		return $out;
	}

	/**
	 * Lookup the value from $src array for this given element.
	 * Handles all name/array resolution automatically.
	 *
	 * Note, this does NOT set the value, only looks up the value from the array.
	 *
	 * @param array $src
	 *
	 * @return mixed
	 */
	public function lookupValueFrom(&$src) {
		$n = $this->get('name');
		if (strpos($n, '[') !== false) {
			$base = substr($n, 0, strpos($n, '['));
			if (!isset($src[$base])) return null;
			$t = $src[$base];
			preg_match_all('/\[(.+?)\]/', $n, $m);
			foreach ($m[1] as $k) {
				if (!isset($t[$k])) return null;
				$t = $t[$k];
			}
			// Now $t should be the value of the POSTed value!
			return $t;
		}
		else {
			if (!isset($src[$n])) return null;
			else return $src[$n];
		}
	}

	/**
	 * Get the appropriate form element based on the incoming type.
	 *
	 * @param string $type
	 * @param array  $attributes
	 *
	 * @return FormElement
	 */
	public static function Factory($type, $attributes = array()) {
		if (!isset(Form::$Mappings[$type])) $type = 'text'; // Default.

		return new Form::$Mappings[$type]($attributes);
	}
}

/**
 * The main Form object.
 */
class Form extends FormGroup {

	/**
	 * Standard mappings for 'text' to class of the FormElement.
	 * This can be extended, ie: wysiwyg or captcha.
	 *
	 * @var array
	 */
	public static $Mappings = array(
		'checkbox'         => 'FormCheckboxInput',
		'checkboxes'       => 'FormCheckboxesInput',
		'file'             => 'FormFileInput',
		'hidden'           => 'FormHiddenInput',
		'pageinsertables'  => 'FormPageInsertables',
		'pagemeta'         => 'FormPageMeta',
		'pagemetas'        => 'FormPageMetasInput',
		'pageparentselect' => 'FormPageParentSelectInput',
		'pagerewriteurl'   => 'FormPageRewriteURLInput',
		'pagethemeselect'  => 'FormPageThemeSelectInput',
		'password'         => 'FormPasswordInput',
		'radio'            => 'FormRadioInput',
		'reset'            => 'FormResetInput',
		'select'           => 'FormSelectInput',
		'state'            => 'FormStateInput',
		'submit'           => 'FormSubmitInput',
		'system'           => 'FormSystemInput',
		'text'             => 'FormTextInput',
		'textarea'         => 'FormTextareaInput',
		'time'             => 'FormTimeInput',
		'wysiwyg'          => 'FormTextareaInput',
	);

	/**
	 * Construct a new Form object
	 *
	 * @param array $atts Array of attribute to assign to this form off the bat.
	 */
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_validattributes = array('accept', 'accept-charset', 'action', 'enctype', 'id', 'method', 'name', 'target', 'style');
		//$this->_attributes['uniqueid'] = rand(1, 4) . Core::RandomHex(7);
		$this->_attributes['method'] = 'POST';
	}

	public function getTemplateName() {
		return 'forms/form.tpl';
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
			$e               = new FormHiddenInput(array('name'  => '___formid',
			                                             'value' => $this->get('uniqueid')));
			$this->_elements = array_merge(array($e), $this->_elements);

			// I need to ensure a repeatable but unique id for this form.
			// Essentially when this form is submitted, I need to be able to know that it's the same form upon re-rendering.
			if (!$this->get('uniqueid')) {
				$hash = '';

				if ($this->get('___modelpks')) {
					foreach ($this->get('___modelpks') as $k => $v) {
						$hash .= $k . ':' . $v . ';';
					}
				}

				foreach ($this->getElements() as $el) {
					// System inputs require the value as well, since they're set by the controller; they're not
					// meant to be changed.
					if($el instanceof FormSystemInput){
						$hash .= get_class($el) . ':' . $el->get('name') . ':' . $el->get('value') . ';';
					}
					else{
						$hash .= get_class($el) . ':' . $el->get('name') . ';';
					}
				}
				// Hash it!
				$hash = md5($hash);
				$this->set('uniqueid', $hash);
				$this->getElementByName('___formid')->set('value', $hash);
			}

			// Was this form already submitted, (and thus saved in the session?
			// If so, render that form instead!  This way the values get transported seemlessly.
			if (isset($_SESSION['FormData'][$this->get('uniqueid')])) {
				if (($savedform = unserialize($_SESSION['FormData'][$this->get('uniqueid')]))) {
					$this->_elements = $savedform->_elements;
				}
				else {
					$ignoreerrors = true;
				}
			}
			else {
				$ignoreerrors = true;
			}
		}

		if ($ignoreerrors) {
			foreach ($this->getElements(true) as $el) {
				$el->setError(false);
			}
		}

		$tpl = new Template();
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
		}

		// Save it
		if (($part === null || $part == 'foot') && $this->get('callsmethod')) {
			$this->saveToSession();
		}

		return $out;
	}

	/**
	 * Get the associated model for this form, if there is one.
	 * This model will also be populated automatically with all the data submitted.
	 *
	 * @return Model
	 */
	public function getModel() {
		$m = $this->get('___modelname');
		if (!$m) return null; // A model needs to be defined first of all...

		$model = new $m();

		if (!$model instanceof Model) return null; // It needs to be a model... :/

		// Page models have special functionality.
		// This is because they are almost always embedded in forms, so they have their own getModel logic,
		// allowing them to be singled out and that model extracted along side the main form's model.
		if($model instanceof PageModel){
			// Find the page and return its model.
			foreach($this->getElements(false, false) as $el){
				if($el instanceof FormPageMeta){
					return $el->getModel();
				}
			}
		}

		// Set the PK's...
		if (is_array($this->get('___modelpks'))) {
			foreach ($this->get('___modelpks') as $k => $v) {
				$model->set($k, $v);
			}

			// It should now be loadable.
			$model->load();
		}

		$model->setFromForm($this, 'model');

		return $model;

		// The below logic in this method is no longer functional yet.


		// Add support for inline Pages for models.
		if ($model->get('baseurl') && $model->getLink('Page') instanceof PageModel && $this->getElementByName('page')) {
			$page = $model->getLink('Page');

			// Update the cached information in the page.
			if ($model->get('title') !== null) $page->set('title', $model->get('title'));
			if ($model->get('access') !== null) $page->set('access', $model->get('access'));

			// Tack on the Page data too!
			$this->getElementByName('page')->getModel($page);
		}


		// Add support for inline Widgets for models.
		if ($model->get('baseurl') && $model->getLink('Widget') instanceof WidgetModel) {
			// All I have to do is just "get" it.... that's it!
			// The save algorithm will do the rest.
			$widget = $model->getLink('Widget');

			// Update the cached information in the page.
			if ($model->get('title') !== null) $widget->set('title', $model->get('title'));
			if ($model->get('access') !== null) $widget->set('access', $model->get('access'));

			// Tack on the Page data too!
			//$this->getElementByName('page')->getModel($page);
		}


		return $model;
	}

	/**
	 * Load this form's values from the provided array, usually GET or POST.
	 * This is really an internal function that should not be called externally.
	 *
	 * @param array $src
	 */
	public function loadFrom($src) {
		$els = $this->getElements(true, false);
		foreach ($els as $e) {
			// Be sure to clear any errors from the previous page load....
			$e->clearError();
			$e->set('value', $e->lookupValueFrom($src));
			if ($e->hasError()) Core::SetMessage($e->getError(), 'error');
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
	 * @return void
	 */
	private function saveToSession() {

		if (!$this->get('callsmethod')) return; // Don't save anything if there's no method to call.

		$this->set('expires', Time::GetCurrent() + 1800); // 30 minutes

		$_SESSION['FormData'][$this->get('uniqueid')] = serialize($this);
	}


	/**
	 * Function that is fired off on page load.
	 * This checks if a form was submitted and that form was present in the SESSION.
	 *
	 * @return null
	 */
	public static function CheckSavedSessionData() {
		// There has to be data in the session.
		if (!(isset($_SESSION['FormData']) && is_array($_SESSION['FormData']))) return;

		$formid = (isset($_REQUEST['___formid'])) ? $_REQUEST['___formid'] : false;
		$form   = false;

		foreach ($_SESSION['FormData'] as $k => $v) {
			// If the object isn't a valid object after unserializing...
			if (!($el = unserialize($v))) {
				unset($_SESSION['FormData'][$k]);
				continue;
			}

			// Check the expires time
			if ($el->get('expires') <= Time::GetCurrent()) {
				unset($_SESSION['FormData'][$k]);
				continue;
			}

			if ($k == $formid) {
				// Remember this for after all the checks have finished.
				$form = $el;
			}
		}

		// No form found... simple enough
		if (!$form) return;

		// Ensure the submission types match up.
		if (strtoupper($form->get('method')) != $_SERVER['REQUEST_METHOD']) {
			Core::SetMessage('Form submission type does not match', 'error');
			return;
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
			if (!$form->hasError()) $status = call_user_func($form->get('callsmethod'), $form);
			else $status = false;
		}
		catch(ModelValidationException $e){
			Core::SetMessage($e->getMessage(), 'error');
			$status = false;
		}

		// Regardless, bundle this form back into the session so the controller can use it if needed.
		$_SESSION['FormData'][$formid] = serialize($form);

		// Fail statuses.
		if ($status === false) return;
		if ($status === null) return;

		// Guess it's not false and not null... must be good then.

		// @todo Handle an internal save procedure for "special" groups such as pageinsertables and what not.

		// Cleanup
		unset($_SESSION['FormData'][$formid]);

		// If it's set to die, simply exit the script without outputting anything.
		if ($status === 'die') exit;
		elseif ($status === true) Core::Reload();
		else Core::Redirect($status);
	}

	/**
	 * Scan through a standard Model object and populate elements with the correct fields and information.
	 *
	 * @param Model $model
	 *
	 * @return Form
	 */
	public static function BuildFromModel(Model $model) {
		$f = new Form();

		// Adding support for grouped items directly from the model :)
		// This will contain the links to the group names if there are any grouped elements.
		// Will make lookups quicker.
		$groups = array();

		// Add the initial model tracker, will remember which model is attached.
		$f->set('___modelname', get_class($model));
		$s = $model->getKeySchemas();
		$i = $model->GetIndexes();
		if (!isset($i['primary'])) $i['primary'] = array();

		$new = $model->isnew();

		if (!$new) {
			// Save the PKs of this model in the SESSION data so they don't have to be sent to the browser.
			$pks = array();
			foreach ($i['primary'] as $k => $v) {
				$pks[$v] = $model->get($v);
			}
			$f->set('___modelpks', $pks);
		}
		/*
		  // Some objects require special attention.
		  if($model instanceof PageModel){
			  $f->addElement('pagemeta', array('name' => 'model', 'model' => $model));
			  return $f;
		  }
  */
		foreach ($s as $k => $v) {
			// Skip the AI column if it doesn't exist.
			if ($new && $v['type'] == Model::ATT_TYPE_ID) continue;

			// These are already taken care above in the SESSION data.
			if (!$new && in_array($k, $i['primary'])) continue;

			// Form attribute defaults
			$formatts = array(
				'type' => null,
				'title' => ucwords($k),
				'description' => null,
				'required' => false,
				'value' => $model->get($k),
				'name' => 'model[' . $k . ']',
			);
			if(!$formatts['value'] && isset($v['default'])) $formatts['value'] = $v['default'];

			// Merge the defaults with the form array if it's present.
			if(isset($v['form'])){
				$formatts = array_merge($formatts, $v['form']);
			}

			// Support the standard attributes too.
			if(isset($v['formtype']))        $formatts['type'] = $v['formtype'];
			if(isset($v['formtitle']))       $formatts['title'] = $v['formtitle'];
			if(isset($v['formdescription'])) $formatts['description'] = $v['formdescription'];
			if(isset($v['required']))        $formatts['required'] = $v['required'];
			if(isset($v['maxlength']))       $formatts['maxlength'] = $v['maxlength'];

			// Boolean checkboxes can have special options.
			//if(isset($v['formtype']) && $v['formtype'] == 'checkbox' && $v['type'] == Model::ATT_TYPE_BOOL){
			//	$el = FormElement::Factory($v['formtype']);
			//	$el->set('options', array('1'));
			//}

			// Standard form types.

			// "disabled" form types are ignored completely.
			if($formatts['type'] == 'disabled'){
				continue;
			}
			// These are based off of the formtype declaration in Model.
			elseif ($formatts['type'] !== null) {
				$el = FormElement::Factory($formatts['type']);
			}
			elseif ($v['type'] == Model::ATT_TYPE_BOOL) {
				$el = FormElement::Factory('radio');
				$el->set('options', array('Yes', 'No'));

				if ($formatts['value']) $formatts['value'] = 'Yes';
				elseif ($formatts['value'] === null && $v['default']) $formatts['value'] = 'Yes';
				elseif ($formatts['value'] === null && !$v['default']) $formatts['value'] = 'No';
				else $formatts['value'] = 'No';
			}
			elseif ($v['type'] == Model::ATT_TYPE_STRING) {
				$el = FormElement::Factory('text');
			}
			elseif ($v['type'] == Model::ATT_TYPE_INT) {
				$el = FormElement::Factory('text');
			}
			elseif ($v['type'] == Model::ATT_TYPE_FLOAT) {
				$el = FormElement::Factory('text');
			}
			elseif ($v['type'] == Model::ATT_TYPE_TEXT) {
				$el = FormElement::Factory('textarea');
			}
			elseif ($v['type'] == Model::ATT_TYPE_CREATED) {
				// This element doesn't need to be in the form.
				continue;
			}
			elseif ($v['type'] == Model::ATT_TYPE_UPDATED) {
				// This element doesn't need to be in the form.
				continue;
			}
			elseif ($v['type'] == Model::ATT_TYPE_ENUM) {
				$el   = FormElement::Factory('select');
				$opts = $v['options'];
				if ($v['null']) $opts = array_merge(array('' => '-Select One-'), $opts);
				$el->set('options', $opts);
				if ($v['default']) $el->set('value', $v['default']);
			}
			else {
				die('Unsupported model attribute type for Form Builder [' . $v['type'] . ']');
			}

			// I no longer need the type attribute.
			unset($formatts['type']);

			// Group support! :)
			if(isset($formatts['group'])){

				$groupname = $formatts['group'];
				if(!isset($groups[$groupname])){
					$groups[$groupname] = new FormGroup(array('title' => $groupname));
					$f->addElement($groups[$groupname]);
				}

				unset($formatts['group']);

				// And set everything else.
				$el->setFromArray($formatts);

				$groups[$groupname]->addElement($el);
			}
			else{
				// And set everything else.
				$el->setFromArray($formatts);

				$f->addElement($el);
			}

		}
		/*
		// If this model supports Pages, add that too!
		if($model->get('baseurl') && $model->getLink('Page') instanceof PageModel){
			// Tack on the page meta inputs for this page.
			// This will include the rewriteurl, parenturl, theme template and page template.
			$f->addElement('pagemeta', $model->getLink('Page'));
		}
		*/
		return $f;
	}
}


class FormPageInsertables extends FormGroup {

	public function  __construct($atts = null) {

		parent::__construct($atts);

		// Some defaults
		if (!$this->get('title')) $this->set('title', 'Page Content');

		// BaseURL needs to be set for this to work.
		if (!$this->get('baseurl')) return null;

		$p = new PageModel($this->get('baseurl'));

		// Ensure I can get the filename.
		$tpl = $p->getTemplateName();
		if (!$tpl) return null;
		$tpl = Template::ResolveFile($tpl);
		if (!$tpl) return null;

		// Scan through $tpl and find any {insertable} tag.
		$tplcontents = file_get_contents($tpl);
		preg_match_all('/\{insertable(.*)\}(.*)\{\/insertable\}/isU', $tplcontents, $matches);

		// Guess this page had no insertables.
		if (!sizeof($matches[0])) return null;
		foreach ($matches[0] as $k => $v) {
			$tag     = trim($matches[1][$k]);
			$content = trim($matches[2][$k]);
			$default = $content;

			// Pull out the name and label of this insertable.
			$name  = preg_replace('/.*name=["\'](.*?)["\'].*/i', '$1', $tag);
			$title = preg_replace('/.*title=["\'](.*?)["\'].*/i', '$1', $tag);

			// No title given?
			if($title == $tag) $title = $name;

			// This insertable may already have content from the database... if so I want to pull that!
			$i = new InsertableModel($this->get('baseurl'), $name);
			if ($i->get('value') !== null) $content = $i->get('value');

			// Determine what the content is intelligently.  (or at least try to...)
			if (strpos($default, "\n") === false && strpos($default, "<") === false) {
				// Regular text insert.
				$this->addElement('text', array('name'  => "insertable[$name]",
				                                'title' => $title,
				                                'value' => $content)
				);
			}
			elseif (preg_match('/<img(.*?)>/i', $default)) {
				// It's an image.
				$this->addElement(
					'file',
					array(
						'name' => 'insertable[' . $name . ']',
						'title' => $title,
						'accept' => 'image/*',
						'basedir' => 'public/insertable',
					)
				);
			}
			else {
				// Just default back to a WYSIWYG.
				$this->addElement('wysiwyg', array('name'  => "insertable[$name]",
				                                   'title' => $title,
				                                   'value' => $content)
				);
			}
		}
	}

	/**
	 * Save the elements back to the database for the bound base_url.
	 */
	public function save() {
		// This is similar to the getModel method of the Form, but is done across multiple records instead of just one.
		$baseurl = $this->get('baseurl');
		$els     = $this->getElements(true, false);
		foreach ($els as $e) {
			if (!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;

			$i = new InsertableModel($baseurl, $matches[1]);
			$i->set('value', $e->get('value'));
			$i->save();
		}
	}

} // class FormPageInsertables


/**
 * Provides inputs for editing:
 * rewriteurl, parenturl, theme template and page template
 * along with page insertables.
 */
class FormPageMeta extends FormGroup {

	public function  __construct($atts = null) {
		// Defaults
		$this->_attributes['name']    = 'page';

		if ($atts instanceof PageModel) {
			parent::__construct(array('name' => 'page'));

			$page = $atts;
		}
		else {
			if(isset($atts['model']) && $atts['model'] instanceof PageModel){
				// Everything is based off the page.
				$page = $atts['model'];
				unset($atts['model']);

				parent::__construct($atts);
			}
			else{
				parent::__construct($atts);

				// BaseURL needs to be set for this to work.
				//if(!$this->get('baseurl')) return null;

				// Everything is based off the page.
				$page = new PageModel($this->get('baseurl'));
			}
		}

		$this->_attributes['baseurl'] = $page->get('baseurl');
		$name = $this->_attributes['name'];

		// I need to get a list of pages to offer as a dropdown for selecting the "parent" page.
		$f = new ModelFactory('PageModel');
		if ($this->get('baseurl')) $f->where('baseurl != ' . $this->get('baseurl'));
		$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');

		$this->addElement(
			'pageparentselect',
			array(
				'name'    => $name . "[parenturl]",
				'title'   => 'Parent Page',
				'value'   => strtolower($page->get('parenturl')),
				'options' => $opts
			)
		);

		// Title
		$this->addElement(
			'text', array(
				      'name'        => $name . "[title]",
				      'title'       => 'Title',
				      'value'       => $page->get('title'),
				      'description' => 'Every page needs a title to accompany it, this should be short but meaningful.',
				      'required'    => true
			      )
		);

		// Rewrite url.
		$this->addElement(
			'pagerewriteurl', array(
				                'name'        => $name . "[rewriteurl]",
				                'title'       => 'Page URL',
				                'value'       => $page->get('rewriteurl'),
				                'description' => 'Starts with a "/", omit ' . ROOT_URL,
				                'required'    => true
			                )
		);

		$this->addElement(
			'access', array(
				        'name'  => $name . "[access]",
				        'title' => 'Access Permissions',
				        'value' => $page->get('access')
			        )
		);

		$this->addElement(
			'pagemetas',
			array(
				'value' => $page->getMetas(),
				'name' => $name . '_meta',
			)
		);

		// Give me all the skins available on the current theme.
		$skins = array('' => '-- Site Default Skin --');
		foreach(ThemeHandler::GetTheme(null)->getSkins() as $s){
			$n = ($s['title']) ? $s['title'] : $s['file'];
			if($s['default']) $n .= ' (default)';
			$skins[$s['file']] = $n;
		}
		if(sizeof($skins) > 2){
			$this->addElement(
				'select', array(
					        'name'    => $name . "[theme_template]",
					        'title'   => 'Theme Skin',
					        'value'   => $page->get('theme_template'),
					        'options' => $skins
				        )
			);
		}

		// Figure out the template directory for custom pages, (if it exists)
		// In order to get the types, I need to sift through all the potential template directories and look for a directory
		// with the matching name.
		$tmpname = substr($page->getBaseTemplateName(), 0, -4) . '/';

		$matches = array();

		$t = new Template();
		foreach($t->getTemplateDir() as $d){
			if(is_dir($d . $tmpname)){
				// Yay, sift through that and get the files!
				$dir = new Directory_local_backend($d . $tmpname);
				foreach($dir->ls() as $file){
					// Skip directories
					if($file instanceof Directory_local_backend) continue;

					/** @var $file File_local_backend */
					if($file->getExtension() != 'tpl') continue;
					$matches[] = $file->getBaseFilename();
				}
			}
		}

		// Are there matches?
		if(sizeof($matches)){
			$pages = array('' => '-- Default Page Template --');
			foreach($matches as $m){
				$pages[$m] = ucwords(str_replace('-', ' ', substr($m, 0, -4))) . ' Template';
			}

			$this->addElement(
				'select',
				array(
					'name'    => $name . '[page_template]',
					'title'   => 'Page Template',
					'value'   => $page->get('page_template'),
					'options' => $pages,
					'class' => 'page-template-selector',
				)
			);
		}
	}

	/**
	 * Save the elements back to the database for the bound base_url.
	 */
	public function save() {

		$page = $this->getModel();

		//if($this->get('cachedaccess')) $page->set('access', $this->get('cachedaccess'));
		//if($this->get('cachedtitle')) $page->set('title', $this->get('cachedtitle'));
		return $page->save();

		// Ensure the children have the right baseurl if that changed.
		$els = $this->getElements();
		foreach ($els as $e) {
			if (!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;
			$e->set('baseurl', $this->get('baseurl'));
		}

		// And all the insertables.
		$i = $this->getElementByName('insertables');
		$i->save();

		return true;
	}

	/**
	 * Get the model for the page subform.
	 * This is on the group because a page object can be set embedded in another form.
	 *
	 * @param null $page
	 *
	 * @return null|PageModel
	 */
	public function getModel($page = null) {
		// Allow linked models.
		if (!$page) $page = new PageModel($this->get('baseurl'));

		// Because name can be changed.
		$name = $this->_attributes['name'];

		$els = $this->getElements(true, false);
		foreach ($els as $e) {

			if (!preg_match('/^[a-z_]*\[(.*?)\].*/', $e->get('name'), $matches)) continue;

			$key = $matches[1];
			$val = $e->get('value');

			// Meta attributes
			if(strpos($e->get('name'), $name . '_meta') === 0){
				$page->setMeta($key, $val);
			}
			elseif(strpos($e->get('name'), $name) === 0){
				$page->set($key, $val);
			}
			else{
				continue;
			}
		}

		return $page;

		// Add in any insertables too, if they're attached.
		// DISABLING 2012.05 cpowell
		/*
		if(($i = $this->getElementByName('insertables'))){
			$els = $i->getElements();
			foreach($els as $e){
				if(!preg_match('#^insertable\[(.*?)\].*#', $e->get('name'), $matches)) continue;

				$submodel = $page->findLink('Insertable', array('name' => $matches[1]));
				$submodel->set('value', $e->get('value'));
			}
		}
		
		return $page;
		*/
	}

	/**
	 * This group does not have a template of its own, should be rendered directly to the form.
	 * @return null
	 */
	public function getTemplateName() {
		return null;
	}

//	

} // class FormPageInsertables

