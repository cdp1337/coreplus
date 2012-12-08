<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 5:17 PM
 * To change this template use File | Settings | File Templates.
 */
class FormTextInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formtextinput';
		$this->_validattributes     = array('accesskey', 'autocomplete', 'dir', 'disabled', 'id', 'lang', 'maxlength', 'name', 'required', 'size', 'tabindex', 'width', 'height', 'value', 'style');
	}
}