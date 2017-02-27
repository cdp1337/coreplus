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
	
	/** @var null|Model If this form element comes from a Model, this is a link back to that model. */
	public $parent = null;

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

	/**
	 * Get the requested attribute from this form element.
	 * 
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		$key = strtolower($key);

		switch ($key) {
			case 'title':
				// This should be translated if necessary.
				$v = isset($this->_attributes[$key]) ? $this->_attributes[$key] : null;
				if($v !== null && strpos($v, 't:') === 0){
					$v = t(substr($v, 2));
				}
				return $v;
			case 'label':
				// Special case, returns either title or name, whichever is set.
				$v = (!empty($this->_attributes['title'])) ? $this->_attributes['title'] : $this->get('name');
				return $v;
			case 'id':
				// ID is also a special case, it can use the name if not defined otherwise.
				return $this->getID();
			case 'options':
				return $this->getOptions();
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
		// System fields are always assumed to be valid, as they can only be set by the controller.
		if($this->get('type') == 'system'){
			return true;
		}
		
		if ($this->get('required') && !$value) {
			// This form element is marked as required but does not have a value assigned!
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

	/**
	 * Simple check to see if there is an error set on this form element.
	 * 
	 * True: there is an error.
	 * False: no error present.
	 * 
	 * @return bool
	 */
	public function hasError() {
		return ($this->_error);
	}

	/**
	 * Get the error string, or null if there is no error.
	 * 
	 * @return string|false
	 */
	public function getError() {
		return $this->_error;
	}

	/**
	 * Set the error message for this form element, optionally displaying it to the browser.
	 * 
	 * @param string $err
	 * @param bool   $displayMessage
	 */
	public function setError($err, $displayMessage = true) {
		$this->_error = $err;
		if ($err && $displayMessage){
			\Core\set_message($err, 'error');
		}
	}

	public function clearError() {
		$this->setError(false);
	}

	public function getTemplateName() {
		$cname = strtolower(get_class($this));
		// Trim off the namespace if there is one.
		// This is a special clause for Core forms and should be refactored!
		if(strpos($cname, 'core\\forms\\') === 0){
			$cname = substr($cname, 11);
		}
		
		// Convert any other namespaces to a directory.
		$cname = str_replace('\\', '/', $cname);
		
		return 'includes/forms/' . $cname . '.tpl';
	}

	/**
	 * Render this form element and return the resulting HTML as a string
	 * 
	 * @return string
	 */
	public function render() {

		// If multiple is set, but the name does not have a [] at the end.... add it.
		if ($this->get('multiple') && !preg_match('/.*\[.*\]/', $this->get('name'))) $this->_attributes['name'] .= '[]';

		$file = $this->getTemplateName();

		$tpl = \Core\Templates\Template::Factory($file);
		
		if($tpl === null){
			return $file . ' could not be located!';
		}

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
		
		// Tack on some system classes 
		if($this->get('required')){
			$classes[] = 'formrequired';
		}
		if($this->hasError()){
			$classes[] = 'formerror';
		}
		if($this->get('disabled')){
			$classes[] = 'formelement-disabled';
		}

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
			// The namespace should be simplified.
			if(strpos($c, 'core\\forms\\') === 0){
				$c = 'form' . substr($c, 11);
			}
			
			// Replace namespace seperators with '-'
			$c = str_replace('\\', '-', $c);
			
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
	 * Get the options for this input as a valid array
	 * 
	 * This array will be translated automatically if necessary.
	 * 
	 * This only has use when called on select, radio, or checkboxes, 
	 * but is listed here to remove the requirement of each of those having the logic.
	 * 
	 * @return array
	 */
	public function getOptions(){
		// Allow options to be pulled dynamically from the source when set.
		if(!isset($this->_attributes['options']) && isset($this->_attributes['source'])){
			// Store this output as the options value so that it doesn't need to be called multiple times.
			$this->_attributes['options'] = $this->_parseSourceAttribute();
		}
		
		if(isset($this->_attributes['options']) && is_array($this->_attributes['options'])){
			// Iterate over each option and translate it if necessary.
			foreach($this->_attributes['options'] as $k => $v){
				if(strpos($v, 't:') === 0){
					$this->_attributes['options'][$k] = t(substr($v, 2));
				}
			}
		}
		
		return $this->_attributes['options'];
	}

	/**
	 * Template helper function
	 * gets the input attributes as a string
	 * @return string
	 */
	public function getInputAttributes() {
		$out = '';
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
	 * Parse the source string and return the resulting output from the method/function set.
	 * 
	 * @return array
	 */
	protected function _parseSourceAttribute(){
		// Select options support a source attribute to be used if there are no options otherwise.
		// this allows the options to be pulled from a dynamic function.
		if(isset($this->_attributes['source'])){
			$source = $this->_attributes['source'];

			if( is_array($source) && sizeof($source) == 2 ){
				// Allow an array of object, method to be called.
				$options = call_user_func($source);
			}
			elseif(strpos($source, 'this::') === 0){
				// This object starts with "this", which should point back to the original Model.
				// This link is now established with the parent object.
				if($this->parent instanceof \Model){
					$m = substr($source, 6);
					$options = call_user_func([$this->parent, $m]);
				}
				else{
					trigger_error('"source => ' . $source . '" requested on ' . $this->get('name') . ' when parent was not defined!  Please only use source when creating a form element from a valid model object.');
					$options = false;
				}
			}
			elseif(strpos($source, '::') !== false){
				// This is a static binding to some model otherwise, great!
				$options = call_user_func($source);
			}
			else{
				// ..... umm
				trigger_error('Invalid source attribute for ' . $this->get('name') . ', please ensure it is set to a callback of a valid class::method!');
				$options = false;
			}

			if($options === false){
				$options = [];
			}
		}
		else{
			// ???......
			$options = [];
		}
		
		return $options;
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
