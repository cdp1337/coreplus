<?php

/**
 * Geo Province form input for picking country+province.
 * 
 * Pulls dynamically from the database for real results.
 */
class GeoProvinceFormInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->classnames[] = 'formelement';
		$this->classnames[] = 'geoprovinceforminput';
		//$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'rows', 'cols');
	}

	public function render(){
		
		$v = $this->get('value');
		
		if(!$v){
			// Default?...
			$v = REMOTE_COUNTRY . ':' . REMOTE_PROVINCE;
		}
		
		// Split them into country and province.
		list($country, $province) = explode(':', $v);

		// Get the provinces for the given selected country, (to save an ajax call)
		$provinces = GeoProvinceModel::Find(['country = ' . $country]);
		$countries = GeoCountryModel::Find(null, null, 'name');

		// Convert the provinces to JSON data so the javascript
		// can use it as if it had loaded from the server.
		$provincejs = [];
		foreach($provinces as $p){
			/** @var GeoProvinceModel $p */
			$provincejs[] = $p->getAsArray();
		}

		$file = $this->getTemplateName();

		$tpl = \Core\Templates\Template::Factory($file);

		$tpl->assign('province', $province);
		$tpl->assign('country', $country);
		$tpl->assign('provinces', $provinces);
		$tpl->assign('province_json', json_encode($provincejs));
		$tpl->assign('countries', $countries);
		$tpl->assign('element', $this);
		$tpl->assign('req', $this->get('required'));

		return $tpl->fetch();
	}
}