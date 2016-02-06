<?php
/**
 * Class file for FormPageMetasInput
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
 *
 */
/**
 * This input element consists of only the seo fields, ie: title, author, description, keywords.
 *
 * @deprecated 2013.07.11 cpowell
 *             Although not strictly deprecated, it is recommended to use the PageModel's built-in form management
 *             via $form->addModel($page, 'page'); instead.
 * @package Core\Forms
 */
class FormPageMetasInput extends FormGroup{

	/**
	 * Function to get the meta tags that are editable herein.
	 */
	private function _getMetas(){

		// This method needs a page model to continue!
		if(!$this->get('model')) return array();

		/** @var $page PageModel */
		$page = $this->get('model');

		return $page->getMetasArray();
	}

	public function get($key){
		if($key == 'value') return $this->getValue();
		else return parent::get($key);
	}



	public function getTemplateName(){
		return 'forms/groups/pagemetas.tpl';
	}

	public function render(){
		//$out = '';
		$prefix = $this->get('name');
		if(!$this->get('title')) $this->set('title', 'Meta Information (SEO)');

		foreach($this->_getMetas() as $name => $dat){

			// The options will start as the array of data.
			$opts = $dat;
			// Don't need this guy
			unset($opts['type']);
			// The name gets updated slightly
			$opts['name'] = $prefix . '[' . $name . ']';

			if(!$this->getElement($opts['name'])){
				$this->addElement( $dat['type'], $opts );
			}

			/*
			$el = FormElement::Factory($dat['type'], array(
				'name'        => ($prefix . '[' . $name . ']'),
				'title'       => $dat['title'],
				'description' => $dat['description'],
				'value'       => $dat['value']
			));
			$out .= $el->render();
			*/
		}
		return parent::render();
	}

	/**
	 * Set the value of this element based on an array.
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public function setValue($value){
		// value is expected to be an array.
		if(is_array($value)){
			$value = json_encode($value);
		}
		elseif(json_decode($value, true)){
			// No change required.
		}
		elseif($value === ''){
			// Blank, also no change required.
		}
		else{
			return false;
		}

		$this->_attributes['value'] = $value;
		return true;
	}

	/**
	 * Get the meta tags contained herein as an array of metas.
	 *
	 * @return array
	 */
	public function getValue(){
		$a = array();
		$prefix = $this->get('name');

		foreach($this->getElements() as $element){
			/** @var $element FormElement */
			$name = substr($element->get('name'), strlen($prefix) + 1, -1);

			if($element->get('value')){
				$a[$name] = $element->get('value');
			}
		} // foreach($this->getElements() as $element)

		return $a;
	}
}

/**
 * Class FormPageMetaAuthorInput provides a text input that pulls autocomplete data from the site users.
 *
 * Most of this form's magick lies within the template.
 *
 * @package Core\Forms
 */
class FormPageMetaAuthorInput extends FormTextInput {
	public function __construct($atts = null){
		parent::__construct($atts);

		$this->_attributes['class'] = 'formelement formpagemetaauthorinput';
	}
}

/**
 * Class FormPageMetaKeywordsInput provides a multi-select textarea for the keywords with autocomplete.
 *
 * Most of this form's magick lies within the template.
 *
 * @package Core\Forms
 */
class FormPageMetaKeywordsInput extends FormTextInput {

	public function __construct($atts = null){

		if($atts === null) $atts = array();

		$atts['multiple'] = true;

		if(isset($atts['model'])){

			$page = $atts['model'];

			if(!$page instanceof PageModel){
				throw new Exception('PageMetaKeywords requires the model attribute to be a valid PageModel object!');
			}

			// Sift through the page and get out all the "keyword" metas.
			// This is a bit different than default because this one form retrieves data from multiple records.
			$atts['value'] = array();
			foreach($page->getLink('PageMeta') as $meta){
				/** @var $meta PageMetaModel */

				// I only am concerned with these.
				if($meta->get('meta_key') != 'keyword') continue;

				$atts['value'][ $meta->get('meta_value') ] = $meta->get('meta_value_title');
			}
		}


		parent::__construct($atts);

		// Find the value key and unset it from the valid attributes.  It's not a standard attribute on this input!
		unset($this->_validattributes[ array_search('value', $this->_validattributes) ]);

		$this->_attributes['class'] = 'formelement formpagemetakeywordsinput';
	}

}