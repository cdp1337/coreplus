<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 5:19 PM
 * To change this template use File | Settings | File Templates.
 */
class FormRadioInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formradioinput';
		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}

	/**
	 * Return the key of the currently checked value.
	 * This will intelligently scan for Yes/No values.
	 */
	public function getChecked() {
		// If this is a boolean (yes/no) radio option and a true or false
		// is set to the value, it should correctly propagate to "Yes" or "No"
		if (!isset($this->_attributes['value'])) {
			return null;
		}
		elseif (
			isset($this->_attributes['options']) &&
			is_array($this->_attributes['options']) &&
			sizeof($this->_attributes['options']) == 2 &&
			isset($this->_attributes['options']['Yes']) &&
			isset($this->_attributes['options']['No'])
		) {
			// Running strtolower on a boolean will result in either "1" or "".
			switch (strtolower($this->_attributes['value'])) {
				case '1':
				case 'true':
				case 'yes':
					return 'Yes';
					break;
				default:
					return 'No';
					break;
			}
		}
		else {
			return $this->_attributes['value'];
		}
	}

}