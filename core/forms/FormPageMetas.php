<?php

/**
 * This input element consists of only the seo fields, ie: title, author, description, keywords.
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
		$metas = $page->getLink('PageMeta');


		$fullmetas = array(
			// The SEO title.  This isn't quite a meta tag, but part of that system regardless, so might as well.
			'title' => array(
				'title'       => 'Search-Optimized Title',
				'description' => 'If a value is entered here, the &lt;title&gt; tag of the page will be replaced with this value.  Useful for making the page more indexable by search bots.',
				'type'        => 'text',
			),
			// Author
			'author' => array(
				'title'       => 'Author',
				'description' => 'Completely optional, but feel free to include it if relevant',
				'type'        => 'text'
			),
			// The author id
			'authorid' => array(
				'type' => 'hidden',
			),
			// Keywords, (the human friendly text of them)
			'keywords' => array(
				'title'       => 'Keywords',
				'description' => 'Provides taxonomy data for this page, separate different keywords with a comma.',
				'type'        => 'pagemetakeywords',
			),
			'description' => array(
				'title'       => 'Description',
				'description' => 'Text that displays on search engine and social network preview links',
				'type'        => 'textarea'
			)
		);

		foreach($fullmetas as $k => $v){
			switch($k){
				case 'keywords':
					$fullmetas[$k]['model'] = $page;
					break;
				case 'author':
					$author = null;
					$authorid = null;
					// Look for the author and author id values from the meta field.
					foreach($metas as $meta){
						/** @var $meta PageMetaModel */
						if($meta->get('meta_key') == 'author'){
							$authorid = $meta->get('meta_value');
							$author = $meta->get('meta_value_title');
							break;
						}
					}
					$fullmetas['author']['value'] = $author;
					$fullmetas['authorid']['value'] = $authorid;
					break;
				case 'authorid':
					// Taken care of in the author case.
					break;
				default:
					$value = null;
					// Look for this key in the set of meta information
					foreach($metas as $meta){
						/** @var $meta PageMetaModel */
						if($meta->get('meta_key') == $k){
							$value = $meta->get('meta_value_title');
							break;
						}
					}
					$fullmetas[$k]['value'] = $value;
					break;
			}
		}

		return $fullmetas;
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

	public function getValue(){
		$a = array();
		$prefix = $this->get('name');

		foreach($this->getElements() as $element){
			/** @var $element FormElement */
			$name = substr($element->get('name'), strlen($prefix) + 1, -1);

			if($element->get('value')){
				if($element->get('multiple') && is_array($element->get('value'))){
					// Flatten them?
					if($element->get('implode')){
						$a[$name] = trim(implode($element->get('implode'), $element->get('value')), $element->get('implode'));
					}
					else{
						$a[$name] = $element->get('value');
					}
				}
				else{
					$a[$name] = $element->get('value');
				}
			} // if($element->get('value'))
		} // foreach($this->getElements() as $element)

		return json_encode($a);
	}
}


class FormPageMetaKeywordsInput extends FormTextInput {

	public function __construct($atts = null){

		if($atts === null) $atts = array();

		$atts['multiple'] = true;

		if(isset($atts['value'])){
			$value = $atts['value'];
		}
		elseif(isset($atts['model'])){

			$page = $atts['model'];

			if(!$page instanceof PageModel){
				throw new Exception('PageMetaKeywords requires a model attribute to be a valid PageModel object!');
			}

			// Sift through the page and get out all the "keyword" metas.
			// This is a bit different than default because this one form retrieves data from multiple records.
			$value = array();
			foreach($page->getLink('PageMeta') as $meta){
				/** @var $meta PageMetaModel */

				// I only am concerned with these.
				if($meta->get('meta_key') != 'keyword') continue;

				$value[ $meta->get('meta_value') ] = $meta->get('meta_value_title');
			}
		}
		else{
			throw new Exception('PageMetaKeywords requires either a model or a value attribute!');
		}

		$atts['value'] = $value;


		parent::__construct($atts);

		// Find the value key and unset it from the valid attributes.  It's not a standard attribute on this input!
		unset($this->_validattributes[ array_search('value', $this->_validattributes) ]);

		$this->_attributes['class'] = 'formelement formpagemetakeywordsinput';
	}

}