<?php
/**
 * File for class GeoAddressFormInput definition in the coreplus project
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131030.1729
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * A short teaser of what AddressForm does.
 *
 * More lengthy description of what AddressForm does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for AddressForm
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 *
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class GeoAddressFormInput extends FormElement {

	/** @var GeoAddressModel */
	private $_model;

	public function __construct($atts = null) {

		$defaults = [
			'class' => 'formelement geoaddressforminput',
			'use_label' => true,
		];
		parent::__construct($atts);

		// Some defaults
		foreach($defaults as $k => $v){
			if(!isset($this->_attributes[$k])){
				$this->_attributes[$k] = $v;
			}
		}
	}

	public function render(){

		// Make sure that some defaults are set first.
		if(!$this->get('name')) $this->set('name', 'address');

		if($this->_model && ($this->_model->exists() || $this->_model->changed())){
			// There is a valid model set, I can pull all the values from that!
			// This should also be used if the model was created but doesn't exist in the database, but was changed.
			// ie: a user entered information on a new model, but had an error that kicked it back.
			// that model may not exist, but it has been changed with the user's data, and so needs to be preserved.
			$v = $this->_model->getAsArray();
		}
		else{
			// There is no model currently set, fine... I'll just use some defaults.
			$v = [
				'id'       => '',
				'label'    => '',
				'address1' => '',
				'address2' => '',
				'city'     => REMOTE_CITY,
				'province' => REMOTE_PROVINCE,
				'postal'   => '',
				'country'  => REMOTE_COUNTRY,
			];
		}

		$id       = $v['id'];
		$label    = $v['label'];
		$address1 = $v['address1'];
		$address2 = $v['address2'];
		$city     = $v['city'];
		$province = $v['province'];;
		$postal   = $v['postal'];
		$country  = $v['country'];

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

		$tpl->assign('id', $id);
		$tpl->assign('use_label', $this->_attributes['use_label']);
		$tpl->assign('label', $label);
		$tpl->assign('address1', $address1);
		$tpl->assign('address2', $address2);
		$tpl->assign('city', $city);
		$tpl->assign('province', $province);
		$tpl->assign('postal', $postal);
		$tpl->assign('country', $country);
		$tpl->assign('provinces', $provinces);
		$tpl->assign('province_json', json_encode($provincejs));
		$tpl->assign('countries', $countries);
		$tpl->assign('element', $this);
		$tpl->assign('req', $this->get('required'));

		return $tpl->fetch();
	}

	/**
	 * Set the value for this address.
	 * Can be either a flat string, (UUID), or an associative array from a form submission.
	 *
	 * @param string|array $value
	 *
	 * @return bool
	 */
	public function setValue($value){
		if($value instanceof GeoAddressModel){
			// A valid model is being passed in
			// Override any model currently set!
			$this->_model = $value;
			return true;
		}
		elseif(is_array($value)){
			// An array of values, (ie from a form submission), is being passed in.

			// Make sure that the model exists so I can populate it.
			if(!$this->_model){
				$this->_model = GeoAddressModel::Construct( (isset($value['id']) && $value['id'] !== '') ? $value['id'] : null );
			}

			if(!($value['address1'] || $value['postal'])){
				// An empty address1 and empty postal code mean it was an empty address, treat it as such.
				$value = null;
			}

			// Now, I can validate the incoming value ^_^
			$valid = $this->validate($value);
			if($valid !== true){
				$this->_error = $valid;
				return false;
			}

			if($value){
				// Value is an array, I can populate it directly!
				$this->_model->setFromArray($value);
			}
			else{
				// Set address1 and postal to empty,
				// these being blank will trigger the Address's save method to delete it instead.
				$this->_model->setFromArray(
					[
						'address1' => '',
						'postal'   => '',
					]
				);
			}

			return true;
		}
		elseif($value){
			// It should be a UUID.
			$this->_model = GeoAddressModel::Construct($value);
			if(!$this->_model->exists()){
				$this->_error = 'Requested address does not exist';
				return false;
			}

			return true;
		}
		else{
			// It's null?...

			$valid = $this->validate($value);
			if($valid !== true){
				$this->_error = $valid;
				return false;
			}

			if($this->_model){
				// Set address1 and postal to empty,
				// these being blank will trigger the Address's save method to delete it instead.
				$this->_model->setFromArray(
					[
						'address1' => '',
						'postal'   => '',
					]
				);
			}

			return true;
		}
	}

	/**
	 * Get a requested attribute from this form element.
	 *
	 * @param string $key The key of the attribute to retrieve
	 *
	 * @return GeoAddressModel|null|string
	 */
	public function get($key){
		if($key == 'value'){
			if(!$this->_model){
				$this->_model = new GeoAddressModel();
			}

			return $this->_model;
		}
		else{
			return parent::get($key);
		}
	}
}