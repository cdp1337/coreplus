<?php
/**
 * All core Form objects in the system
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * Class FormGroup is the standard parent of any form or group of form elements that have children.
 *
 * @package Core\Forms
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

	/**
	 * @var bool Persistent elements are sticky on the form between page loads.  Automatically set to true/false during submissions.
	 */
	public $persistent = true;

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

	/**
	 * Add a given element, (or element type with attributes), onto this form or form group.
	 *
	 * @param            $element
	 * @param null|array $atts
	 */
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

	public function addElementAfter($newelement, $currentelement){
		if(is_string($currentelement)){
			// I need to convert this to an element.
			$currentelement = $this->getElement($currentelement);
			if(!$currentelement){
				// Cannot locate element by name... can't add after.
				return false;
			}
		}

		foreach ($this->_elements as $k => $el) {
			// A match found?  Replace it!
			if($el == $currentelement){
				// Splice this new element into the array.
				// I need to do $k+1 because $elements is a zero-based index.
				// If it's the first element in the stack, that would be index 0, but I want after that, so it
				// needs to shift to element index 1.
				array_splice($this->_elements, $k+1, 0, [$newelement]);
				return true;
			}

			// If the element was another group, tell that group to scan too!
			if ($el instanceof FormGroup) {
				// Scan this object too!
				if ($el->addElementAfter($newelement, $currentelement)) return true;
			}
		}

		return false;
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
	 * Useful for automatically generated forms and working backwards instead of forward, (sometimes you only
	 * want to remove one or two fields instead of creating twenty).
	 *
	 * @param string $name The name of the element to remove.
	 * @return boolean
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

		// There is a form on the page, do not allow caching.
		\Core\view()->disableCache();
		$tpl = \Core\Templates\Template::Factory($file);
		$tpl->assign('group', $this);
		$tpl->assign('elements', $out);
		return $tpl->fetch();
	}

	/**
	 * Template helper function
	 * gets the css class of the element.
	 * @return string
	 */
	public function getClass() {

		$classnames = [];

		// class can contain multiple classes.
		if($this->get('class')){
			$classnames = explode(' ', $this->get('class'));
		}

		if($this->get('required')){
			$classnames[] = 'formrequired';
		}

		if($this->hasError()){
			$classnames[] = 'formerror';
		}

		if($this->get('orientation')){
			$classnames[] = 'form-orientation-' . $this->get('orientation');
		}

		// Remove dupes
		$classnames = array_unique($classnames);
		// And sort, just for the lulz of it.
		sort($classnames);

		// And return a flattened list
		return implode(' ', $classnames);
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
			// Names such as config[/blah/foo] are valid, but throw IDs for a loop when config-/blah/foo is rendered!
			$n = str_replace(['/', '[', ']'], '-', $this->get('name'));
			// Convert the rest of the characters to valid URl characters.
			$n = \Core\str_to_url($n);
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
	 * Get all elements by *regex* name.
	 *
	 * Useful for checkboxes, multi inputs, and other groups of input elements.
	 *
	 * <h3>Example Usage</h3>
	 * <code class="php"><pre>
	 * The HTML form:
	 * &lt;input name="values[123]"/&gt;
	 * &lt;input name="values[124]"/&gt;
	 * &lt;input name="values[125]"/&gt;
	 *
	 * The PHP code:
	 * $form->getElementsByName('values\[.*\]');
	 * </pre></code>
	 *
	 * @param $nameRegex string The regex-friendly name of the elements to return.
	 *
	 * @return array
	 */
	public function getElementsByName($nameRegex){
		$ret = [];
		$els = $this->getElements(true, true);

		// Determine which delimiter to use based on what's NOT present.
		if(strpos($nameRegex, '#') === false){
			$nameRegex = '#' . $nameRegex . '#';
		}
		else{
			$nameRegex = '#' . str_replace('#', '\#', $nameRegex) . '#';
		}

		foreach ($els as $el) {
			if(preg_match($nameRegex, $el->get('name')) === 1){
				$ret[] = $el;
			}
		}

		return $ret;
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

	/**
	 * Shortcut to get the child element's value
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getElementValue($name){
		$el = $this->getElement($name);
		if(!$el){
			return null;
		}

		return $el->get('value');
	}
}

/**
 * Class FormElement is the base object for all elements
 *
 * @package Core\Forms
 */
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

	/**
	 * @var bool Persistent elements are sticky on the form between page loads.
	 */
	public $persistent = true;

	public $classnames = array();

	public function __construct($atts = null) {

		if ($atts) $this->setFromArray($atts);
	}

	public function set($key, $value) {
		$key = strtolower($key);

		switch ($key) {
			case 'class':
				$this->classnames[] = $value;
				break;
			case 'value': // Drop into special logic.
				$this->setValue($value);
				break;
			case 'label': // This is an alias for title.
				$this->_attributes['title'] = $value;
				break;
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
				if($value === false || $value === '0' | $value === 0 || $value === 'off'){
					$this->_attributes[$key] = 'off';
				}
				elseif($value === true || $value === '1' || $value === 1 || $value === 'on' || $value === ''){
					$this->_attributes[$key] = 'on';
				}
				else{
					// Resolve this to an actual URL using Core's built-in resolution system.
					$this->_attributes[$key] = \Core\resolve_link($value);
				}
				break;
			case 'persistent':
				$this->persistent = $value;
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

		// A hot-patch to add better support for user-submitted URLs.
		// This poses an issue because a user may enter "google.com" when asked to enter a URL.
		// This script should translate any non-prefixed value with a generic http:// prefix.
		// @todo If more use cases like this are needed, it would make sense to implement a translateValue hook!
		if(isset($this->_attributes['validation']) && $this->_attributes['validation'] == Model::VALIDATION_URL_WEB){
			if(trim($value) != '' && strpos($value, '://') === false){
				$value = 'http://' . $value;
			}
		}

		$valid = $this->validate($value);
		if($valid !== true){
			$this->_error = $valid;
			return false;
		}

		$this->_attributes['value'] = $value;
		return true;
	}

	/**
	 * Validate a given value for this form element.
	 * Will use the extendable validation logic if provided.
	 *
	 * @param mixed $value
	 * @return string|boolean String if an error was encountered, otherwise TRUE if no errors.
	 */
	public function validate($value){
		if ($this->get('required') && !$value) {
			return $this->get('label') . ' is required.';
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
				return $vmesg;
			}
			// regex-based validation.  These don't have any return strings so they're easier.
			elseif (
				($v{0} == '/' && !preg_match($v, $value)) ||
				($v{0} == '#' && !preg_match($v, $value))
			) {
				if (DEVELOPMENT_MODE) $vmesg .= ' validation used: ' . $v;
				return $vmesg;
			}
		}

		// No errors received!
		return true;
	}

	/**
	 * Get the value of this element as a string
	 * In select options, this will be the label of the option.
	 *
	 * @return string
	 */
	public function getValueTitle(){
		$v = $this->get('value');

		if($v === '' || $v === null) return null;

		if($this->get('options') && isset($this->_attributes['options'][$v])) return $this->_attributes['options'][$v];
		else return $v;
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

		$tpl = \Core\Templates\Template::Factory($file);

		$tpl->assign('element', $this);

		return $tpl->fetch();
	}

	/**
	 * Template helper function
	 * gets the css class of the element.
	 * @return string
	 */
	public function getClass() {
		$classes = array_merge($this->classnames, explode(' ', $this->get('class')));
		if($this->get('required')) $classes[] = 'formrequired';
		if($this->hasError()) $classes[] = 'formerror';

		return implode(' ', array_unique($classes));
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
			// Names such as config[/blah/foo] are valid, but throw IDs for a loop when config-/blah/foo is rendered!
			$n = str_replace(['/', '[', ']'], '-', $this->get('name'));
			// Convert the rest of the characters to valid URl characters.
			$n = \Core\str_to_url($n);
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

		if(isset($this->_attributes['source']) && !isset($this->_attributes['options'])){
			// Validation exists... check it.
			$source = $this->_attributes['source'];


			if(
				// Allow an array of object, method to be called.
				// This is generally only used with the Model's "this::foo" construct.
				(is_array($source) && sizeof($source) == 2) ||

				// Allow the source to be specified as a static public function
				strpos($source, '::') !== false
			){
				// the method can either be true, false or a string.
				// Only if true is returned will that be triggered as success.
				$this->_attributes['options'] = call_user_func($source);
			}
		}

		foreach ($this->_validattributes as $k) {
			if (
				$k == 'required' ||
				$k == 'disabled' || $k == 'checked'
			) {
				// These are all $k = $k if they're enabled.
				if(!$this->get($k)) {
					continue;
				}
				else {
					$out .= sprintf(' %s="%s"', $k, $k);
				}
			}
			elseif(($v = $this->get($k)) !== null) {
				$out .= " $k=\"" . str_replace('"', '&quot;', $v) . "\"";
			}
		}

		// Find any "data-" attribute too!
		foreach($this->_attributes as $k => $v){
			if(strpos($k, 'data-') === 0){
				// Allow all data- attributes to simply be passed in verbatim.
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
		'access'           => 'FormAccessStringInput',
		'button'           => 'FormButtonInput',
		'checkbox'         => 'FormCheckboxInput',
		'checkboxes'       => 'FormCheckboxesInput',
		'date'             => 'FormDateInput',
		'datetime'         => 'FormDateTimeInput',
		'file'             => 'FormFileInput',
		'hidden'           => 'FormHiddenInput',
		'license'          => 'FormLicenseInput',
		'markdown'         => 'FormMarkdownInput',
		'pageinsertables'  => 'FormPageInsertables',
		'pagemeta'         => 'FormPageMeta',
		'pagemetas'        => 'FormPageMetasInput',
		'pagemetaauthor'   => 'FormPageMetaAuthorInput',
		'pagemetakeywords' => 'FormPageMetaKeywordsInput',
		'pageparentselect' => 'FormPageParentSelectInput',
		'pagerewriteurl'   => 'FormPageRewriteURLInput',
		'pagethemeselect'  => 'FormPageThemeSelectInput',
		'pagepageselect'   => 'FormPagePageSelectInput',
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
		'user'             => 'FormUserInput',
		'wysiwyg'          => 'FormTextareaInput',
	);

	public static $GroupMappings = array(
		'tabs'             => 'FormTabsGroup',
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
			/** @var Model $model */
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
		if(!$this->referrer && isset($_SERVER['HTTP_REFERER'])){
			$this->referrer = $_SERVER['HTTP_REFERER'];
		}
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
			else $class = 'FormGroup'; // Default.

			$ref = new ReflectionClass($class);
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
	 * @return Model
	 */
	public function getModel($prefix = 'model') {

		// A model needs to be defined first of all...
		if(!isset($this->_models[$prefix])){
			return null;
		}
		/** @var $model Model */
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
				Core::SetMessage($e->getError(), 'error');
			}
		}
	}

	/**
	 * Add a model's rendered elements to this form.
	 *
	 * All models must have a common prefix, generally this is "model", but if multiple models are on one form,
	 *  then different prefixes can be used.
	 *
	 * @param Model  $model  The model to populate elements from
	 * @param string $prefix The prefix to create elements as
	 */
	public function addModel(Model $model, $prefix = 'model'){

		// Is this model already attached?
		if(isset($this->_models[$prefix])) return;

		// Adding support for grouped items directly from the model :)
		// This will contain the links to the group names if there are any grouped elements.
		// Will make lookups quicker.
		$groups = array();

		$this->_models[$prefix] = $model;

		// Add the initial model tracker, will remember which model is attached.
		//$this->set('___' . $prefix . 'name', get_class($model));
		$s = $model->getKeySchemas();
		$i = $model->GetIndexes();
		if (!isset($i['primary'])) $i['primary'] = array();

		$new = $model->isnew();

		//if (!$new) {
		//	// Save the PKs of this model in the SESSION data so they don't have to be sent to the browser.
		//	$pks = array();
		//	foreach ($i['primary'] as $k => $v) {
		//		$pks[$v] = $model->get($v);
		//	}
		//	$this->set('___' . $prefix . 'pks', $pks);
		//}

		foreach ($s as $k => $v) {
			/*
			// Skip the AI column if it doesn't exist.
			if ($new && $v['type'] == Model::ATT_TYPE_ID) continue;

			// Skip the UUID column if it doesn't exist too.
			if($new && $v['type'] == Model::ATT_TYPE_UUID) continue;

			// These are already taken care above in the SESSION data.
			if (!$new && in_array($k, $i['primary'])) continue;
			*/

			// Base required keys for all form elements.
			// These get mapped by the necessary fields.
			$formatts = array(
				'type' => null,
				'title' => ucwords(str_replace('_', ' ', $k)),
				'description' => null,
				'required' => false,
				'value' => $model->get($k),
				'name' => $prefix . '[' . $k . ']',
			);

			// Default options, these are set by the conditional switch below, and are only set if the $formatts do not contain them.
			$defaults = [];

			if($formatts['value'] === null && isset($v['default'])) $formatts['value'] = $v['default'];

			// Default values from the standard (outside the form set), attributes.
			if(isset($v['formtype']))        $formatts['type'] = $v['formtype'];
			if(isset($v['formtitle']))       $formatts['title'] = $v['formtitle'];
			if(isset($v['formdescription'])) $formatts['description'] = $v['formdescription'];
			if(isset($v['required']))        $formatts['required'] = $v['required'];
			if(isset($v['maxlength']))       $formatts['maxlength'] = $v['maxlength'];

			// Merge the defaults with the form array if it's present.
			// This is the most precise method for setting form attributes, and is therefore authoritative above the generic ones.
			if(isset($v['form'])){
				$formatts = array_merge($formatts, $v['form']);
			}



			//// IDs, FOREIGN IDs, UUIDs, ETC

			if($formatts['type'] == 'disabled'){
				// "disabled" form types are ignored completely.
				continue;
			}
			elseif ($v['type'] == Model::ATT_TYPE_ID){
				$el = FormElement::Factory('system');
				// These are handled automatically.
				$formatts['required'] = false;
			}
			elseif ($v['type'] == Model::ATT_TYPE_ID_FK){
				$el = FormElement::Factory('system');
				// These are handled automatically.
				$formatts['required'] = false;
			}
			elseif($v['type'] == Model::ATT_TYPE_UUID){
				$el = FormElement::Factory('system');
				// These are handled automatically.
				$formatts['required'] = false;
			}
			elseif($v['type'] == Model::ATT_TYPE_UUID_FK && $formatts['type'] === null){
				// Allow foreign keys to be represented as other form types if there is a form type set!
				$el = FormElement::Factory('system');
				// These are handled automatically.
				$formatts['required'] = false;
			}

			//// FORM TYPE ATTRIBUTES

			elseif($formatts['type'] == 'datetime' && $v['type'] == Model::ATT_TYPE_INT){
				$defaults['datetimepicker_dateformat'] = 'yy-mm-dd';
				$defaults['datetimepicker_timeformat'] = 'HH:mm';
				$defaults['displayformat'] = 'Y-m-d H:i';
				$defaults['saveformat'] = 'U';
				$el = FormElement::Factory('datetime');
			}
			elseif ($formatts['type'] !== null) {
				$el = FormElement::Factory($formatts['type']);
			}

			// MODEL TYPE ATTRIBUTES ONLY

			elseif ($v['type'] == Model::ATT_TYPE_BOOL) {
				$el = FormElement::Factory('radio');
				$el->set('options', array('Yes', 'No'));

				if ($formatts['value']) $formatts['value'] = 'Yes';
				elseif ($formatts['value'] === null && $v['default']) $formatts['value'] = 'Yes';
				elseif ($formatts['value'] === null && !$v['default']) $formatts['value'] = 'No';
				else $formatts['value'] = 'No';
			}
			elseif ($v['type'] == Model::ATT_TYPE_SITE) {
				$el = FormElement::Factory('system');
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
			elseif ($v['type'] == Model::ATT_TYPE_DELETED) {
				// This element doesn't need to be in the form.
				continue;
			}
			elseif ($v['type'] == Model::ATT_TYPE_ALIAS) {
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
			elseif($v['type'] == Model::ATT_TYPE_ISO_8601_DATE){
				$defaults['datepicker_dateformat'] = 'yy-mm-dd';
				$el = FormElement::Factory('date');
			}
			elseif($v['type'] == Model::ATT_TYPE_ISO_8601_DATETIME){
				$defaults['datetimepicker_dateformat'] = 'yy-mm-dd';
				$defaults['datetimepicker_timeformat'] = 'HH:mm';
				$defaults['saveformat'] = 'Y-m-d H:i:00';
				$el = FormElement::Factory('datetime');
			}
			else {
				die('Unsupported model attribute type for Form Builder [' . $v['type'] . ']');
			}

			// I no longer need the type attribute.
			unset($formatts['type']);

			// Merge in any defaults, (without overriding).
			foreach($defaults as $k => $v){
				if(!isset($formatts[$k])) $formatts[$k] = $v;
			}

			// Add special functionality here to allow passing in "this" as a valid reference for the "source" attribute.
			// This will allow the instantiated Model object as a whole to be used as a reference when retriving options.
			if(isset($formatts['source']) && strpos($formatts['source'], 'this::') === 0){
				$formatts['source'] = [$model, substr($formatts['source'], 6)];
			}

			// And set everything else.
			$el->setFromArray($formatts);

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

		$this->set('expires', (int)Time::GetCurrent() + 1800); // 30 minutes

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
			if ($el->get('expires') <= Time::GetCurrent()) {
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
			Core::SetMessage('Form submission type does not match', 'error');
			return;
		}

		// Ensure the REFERRER and original URL match up.
		if($_SERVER['HTTP_REFERER'] != $form->originalurl){
			// @todo This is reported to be causing issues with production sites.
			//       If found true, this check may need to be removed / refactored.
			//Core::SetMessage('Form submission referrer does not match, please try your submission again.', 'error');
			SystemLogModel::LogInfoEvent(
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
			if (!$form->hasError()) $status = call_user_func($form->get('callsmethod'), $form);
			else $status = false;
		}
		catch(ModelValidationException $e){
			Core::SetMessage($e->getMessage(), 'error');
			$status = false;
		}
		catch(GeneralValidationException $e){
			Core::SetMessage($e->getMessage(), 'error');
			$status = false;
		}
		catch(Exception $e){
			if(DEVELOPMENT_MODE){
				// Developers get the full message
				Core::SetMessage($e->getMessage(), 'error');
			}
			else{
				// While users of production-enabled sites get a friendlier message.
				Core::SetMessage('Oops, something went wrong while submitting the form.  The administrator has been notified of this issue, please try again later.', 'error');
			}
			Core\ErrorManagement\exception_handler($e);
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
		elseif($status === REL_REQUEST_PATH){
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
	 * @param Model $model
	 *
	 * @return Form
	 */
	public static function BuildFromModel(Model $model) {
		$f = new Form();
		$f->addModel($model);
		return $f;
	}
}

