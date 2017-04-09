<?php
/**
 * All core Form objects in the system
 *
 * @package Core\Forms
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

namespace Core\Forms;

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
