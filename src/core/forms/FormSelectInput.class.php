<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 5:19 PM
 * To change this template use File | Settings | File Templates.
 */
class FormSelectInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formselect';
		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'rows', 'cols');
	}
}