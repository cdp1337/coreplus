<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 5:15 PM
 * To change this template use File | Settings | File Templates.
 */
class FormCheckboxInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formcheckboxinput';
		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}

	/*public function get($key) {
		if($key == 'value' && sizeof($this->_attributes['options']) > 1){
			// This should return an array if there are more than 1 option.
			if(!$this->_attributes['value']) return array();
			else return $this->_attributes['value'];
		}
		else{
			return parent::get($key);
		}
	}*/

	/*public function set($key, $value) {
		if($key == 'value'){
			if($value) $this->_attributes['value'] = true;
			else $this->_attributes['value'] = false;
		}
		else{
			return parent::set($key, $value);
		}
	}*/

}