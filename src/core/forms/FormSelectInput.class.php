<?php
/**
 * Class file for FormSelectInput
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

/**
 * Class FormSelectInput provides a select box
 *
 * @package Core\Forms
 */
class FormSelectInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formselect';
		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'rows', 'cols');
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
		
		// Allow options to be pulled dynamically from the source when set.
		if($key == 'options' && !isset($this->_attributes['options']) && isset($this->_attributes['source'])){
			// Store this output as the options value so that it doesn't need to be called multiple times.
			$this->_attributes['options'] = $this->_parseSourceAttribute();
		}
		
		// Carry on!
		return parent::get($key);
	}

	/**
	 * Parse the source string and return the resulting output from the method/function set.
	 * 
	 * @return array
	 */
	private function _parseSourceAttribute(){
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
				if($this->parent instanceof Model){
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
}