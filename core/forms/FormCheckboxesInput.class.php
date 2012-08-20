<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 5:16 PM
 * To change this template use File | Settings | File Templates.
 */
class FormCheckboxesInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formcheckboxesinput';
		$this->_attributes['multiple'] = true;
		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}

	public function get($key) {
		if ($key == 'value' && sizeof($this->_attributes['options']) > 1) {
			// This should return an array if there are more than 1 option.
			if(!isset($this->_attributes['value'])) return array();
			elseif (!$this->_attributes['value']) return array();
			else return $this->_attributes['value'];
		}
		else {
			return parent::get($key);
		}
	}

	public function set($key, $value) {
		if ($key == 'options') {
			// The options need to be an array, (hence the plural use)
			if (!is_array($value)) return false;

			// if every key in this is an int, transpose the value over to the key instead.
			// This allows for having an option with a different title and value.
			// (and cheating, not actually checking every key)
			if (isset($value[0]) && isset($value[sizeof($value) - 1])) {
				foreach ($value as $k => $v) {
					unset($value[$k]);
					$value[$v] = $v;
				}
			}

			// Please note, this system CANNOT call the parent function, because its default behaviour is to
			// remap any int-based array to value/value pairs.  Since that logic is already handled here, there
			// is no need to perform it again, (of which it performs incorrectly)...
			$this->_attributes[$key] = $value;
			return true;
			//return parent::set($key, $value);
		}
		else {
			return parent::set($key, $value);
		}
	}

}