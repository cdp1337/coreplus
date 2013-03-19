<?php

class FormCaptchaInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formcaptchainput';
		$this->_validattributes = array('id', 'name', 'required', 'tabindex', 'style');
	}
	
	public function render() {
		if(!isset($this->_attributes['title'])) $this->_attributes['title'] = 'Are you a Human?';
		
		return parent::render();
	}
	
	public function setValue($value) {
		if(!$value){
			$this->_error = $this->get('title') . ' is required.';
			return false;
		}
		if($value != $_SESSION['captcha']){
			$this->_error = $this->get('title') . ' does not match image.';
			return false;
		}
		
		parent::setValue('');
	}
}