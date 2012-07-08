<?php

class FormPageMetasInput extends FormElement{

	/**
	 * Function to get the meta tags that are editable herein.
	 */
	private function _getMetas(){
		$metas = $this->get('value');
		// Try to decode these values if possible.
		$metas = json_decode($metas, true);
		// Else, a blank array works well enough.
		if(!$metas) $metas = array();

		$fullmetas = array(
			// Author
			'author' => array(
				'title'       => 'Author',
				'description' => 'Completely optional, but feel free to include it if relevant',
				'type'        => 'text'
			),
			'keywords' => array(
				'title'       => 'Keywords',
				'description' => 'Helps search engines classify this page',
				'type'        => 'text'
			),
			'description' => array(
				'title'       => 'Description',
				'description' => 'Text that displays on search engine and social network preview links',
				'type'        => 'textarea'
			)
		);

		foreach($fullmetas as $k => $v){
			$fullmetas[$k]['value'] = (isset($metas[$k])) ? $metas[$k] : null;
		}

		return $fullmetas;
	}

	public function render(){
		$out = '';
		$prefix = $this->get('name');

		foreach($this->_getMetas() as $name => $dat){
			$el = FormElement::Factory($dat['type'], array(
				'name'        => ($prefix . '[' . $name . ']'),
				'title'       => $dat['title'],
				'description' => $dat['description'],
				'value'       => $dat['value']
			));
			$out .= $el->render();
		}
		return $out;
	}

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
}

class FormPageThemeSelectInput extends FormSelectInput{
	public function render(){

		// Give me all the skins available on the current theme.
		$skins = array('' => '-- Site Default Skin --');
		foreach(ThemeHandler::GetTheme(null)->getSkins() as $s){
			$n = ($s['title']) ? $s['title'] : $s['file'];
			if($s['default']) $n .= ' (default)';
			$skins[$s['file']] = $n;
		}
		if(sizeof($skins) <= 2){
			// This will just return a blank string, as it does not need to be rendered to the UA.
			return '';
		}

		// Set the options as the themes available.
		$this->set('options', $skins);

		// And continue!
		return parent::render();
	}
}

class FormPageParentSelectInput extends FormSelectInput{
	public function render(){
		$f = new ModelFactory('PageModel');
		if ($this->get('baseurl')) $f->where('baseurl != ' . $this->get('baseurl'));
		$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');

		$this->set('options', $opts);

		// And continue!
		return parent::render();
	}
}