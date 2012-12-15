<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 7:13 PM
 * To change this template use File | Settings | File Templates.
 */

class FormSystemInput extends FormElement{

	/**
	 * "System" inputs do not get sent to the browser, so they have no rendered text.
	 * @return string
	 */
	public function render(){
		return '';
	}

	public function lookupValueFrom(&$src){
		// Since system inputs don't actually change based on user input... just return this value.
		return $this->get('value');
	}
}
