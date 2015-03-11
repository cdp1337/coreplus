<?php
/**
 * Class file for the model GeoAddressModel
 *
 * @package Geographic Codes
 * @author Charlie Powell <charlie@eval.bz>
 */
class GeoAddressModel extends Model {
	/**
	 * Schema definition for AddressModel
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_UUID
		),
		'label' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 100,
			'form' => [
				'title' => 'Label',
			],
		),
		'address1' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'form' => [
				'title' => 'Address 1',
			],
		),
		'address2' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'form' => [
				'title' => 'Address 2',
			],
		),
		'city' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'province' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 3,
			'comment' => 'Two or three digit ISO 3166-2 code',
			'form' => [
				'type' => 'state',
				'title' => 'State/Province',
			],
		),
		'postal' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 20,
			'form' => [
				'title' => 'Zip/Postal',
			],
		),
		'country' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 2,
			'default' => 'US',
			'comment' => 'Two-digit country code',
			'form' => array(
				'type' => 'select',
			),
		),
		'beansbooks_id' => [
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'If the beansbooks module is installed, this may be the beans ID of the customer',
			'formtype' => 'disabled',
		],
		'lat' => array(
			'type' => Model::ATT_TYPE_FLOAT,
			'precision' => '17,11',
			'default' => 0,
			'formtype' => 'hidden',
			'null' => true,
			'comment' => 'Latitude of this location',
		),
		'lng' => array(
			'type' => Model::ATT_TYPE_FLOAT,
			'precision' => '17,11',
			'default' => 0,
			'formtype' => 'hidden',
			'null' => true,
			'comment' => 'Longitude of this location',
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	/**
	 * Index definition for AddressModel
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);

	/**
	 * Check to see if this model is "blank".
	 *
	 * This does not mean not filled out, but instead means no address1 and no postal code.
	 *
	 * @return bool
	 */
	public function isBlank(){
		$a1 = $this->get('address1');
		$po = $this->get('postal');

		return ($a1 == '' && $po == '');
	}

	public function save(){
		// Quick check to see if this address is actually populated or not.
		if( $this->_data['address1'] == '' && $this->_data['postal'] == '' ){
			if($this->exists()){
				$ret = $this->delete();
				$this->_data['id'] = null;
				return $ret;
			}
			else{
				// No change!
				return false;
			}
		}
		elseif($this->changed()){

			if(Core::IsLibraryAvailable('GoogleMaps')){
				// If google maps are available, use that to geocode this address.
				$req = new Google\Maps\GeocodeRequest();
				$req->address1 = $this->get('address1');
				$req->address2 = $this->get('address2');
				$req->city     = $this->get('city');
				$req->state    = $this->get('province');
				$req->postal   = $this->get('postal');
				$req->country  = $this->get('country');

				$lookup = $req->lookup();

				if($lookup->isValid()){
					$this->set('lat', $lookup->getLat());
					$this->set('lng', $lookup->getLng());
				}
				else{
					$this->set('lat', 0);
					$this->set('lng', 0);
				}
			}

			// Resume with the traditional save!
			return parent::save();
		}
		else{
			// No change!
			return false;
		}
	}

	/**
	 * Get the City/Province/Postal line formatted based on the country of this address.
	 *
	 * @return string
	 */
	public function getCPPFormatted(){
		switch($this->get('country')){
			case 'CA':
				$out = $this->get('city');
				if($this->get('province') || $this->get('postal')){
					$out .= ' ';
				}

				if($this->get('province')){
					$out .= $this->get('province');
					if($this->get('postal')){
						$out .= ' ';
					}
				}

				if($this->get('postal')){
					$out .= ' ' . $this->get('postal');
				}
				return $out;
			case 'US':
			default:
				$out = $this->get('city');
				if($this->get('province') || $this->get('postal')){
					$out .= ', ';
				}

				if($this->get('province')){
					$out .= $this->get('province');
					if($this->get('postal')){
						$out .= ' ';
					}
				}

				if($this->get('postal')){
					$out .= $this->get('postal');
				}
				return $out;
		}
	}

	/**
	 * Get the City/State/Zip line formatted based on the country of this address.
	 *
	 * Alias of getCPPFormatted()
	 *
	 * @return string
	 */
	public function getCSZFormatted(){
		return $this->getCPPFormatted();
	}

	/**
	 * Get the City/State/Postal line formatted based on the country of this address.
	 *
	 * Alias of getCPPFormatted()
	 *
	 * @return string
	 */
	public function getCSPFormatted(){
		return $this->getCPPFormatted();
	}

	/**
	 * Get the human-readable label for this record.
	 *
	 * By default, it will sift through the schema looking for keys that appear to be human-readable terms,
	 * but for best results, please extend this method and have it return what's necessary for the given Model.
	 *
	 * @return string
	 */
	public function getLabel(){

		$lines = [];

		if($this->get('label')){
			$lines[] = $this->get('label');
		}

		$lines[] = $this->get('address1');
		if($this->get('address2')){
			$lines[] = $this->get('address2');
		}
		$lines[] = $this->getCPPFormatted();

		return implode("<br/>\n", $lines);
	}

	/**
	 * Get the BeansBooks keys for this object
	 */
	public function getBeansKeys(){
		return [
			'standard' => 'label',
			'address1' => 'address1',
			'address2' => 'address2',
			'city'     => 'city',
			'state'    => 'province',
			'zip'      => 'postal',
			'country'  => 'country',
			'id'       => 'beansbooks_id',
		];
	}

	/**
	 * Populate this model with data directly from BeansBooks
	 *
	 * @param $address
	 *
	 * @throws Exception
	 */
	public function setFromBeansObject($address) {
		/** @noinspection PhpUndefinedNamespaceInspection This method is only available if the BeansBooks module is installed. */
		if(!(
			$address instanceof \BeansBooks\Objects\CustomerAddress ||
			$address instanceof \BeansBooks\Objects\VendorAddress
		)){
			throw new Exception('Please only set an address from a BeansBooks CustomerAddress or VendorAddress.');
		}

		$keys = $this->getBeansKeys();

		foreach($keys as $rk => $lk){
			$rv = $address->get($rk);

			if($lk == 'state'){
				if(strlen($rv) > 3){
					// They typed in the state name here?
					// This needs to be the state code.
					$province = GeoProvinceModel::Find(['country = ' . $address->get('country'), 'name = ' . $rv], 1);
					if($province){
						$rv = $province->get('code');
					}
				}
			}

			$this->set($lk, $rv);
		}
	}

	/**
	 * Populate BeansBooks with data from this model, (and sync back any changes afterwards too).
	 *
	 * This requires an additional array because beans has additional metainfo about the address than the GeoAddressModel stores,
	 * such as customer or vendor associated with the address.
	 *
	 * As such, a second array is required to be passed in to provide any of this metadata.
	 *
	 * @param $address \BeansBooks\Objects\CustomerAddress|\BeansBooks\Objects\VendorAddress
	 * @param $additionalData array
	 *
	 * @throws Exception
	 */
	public function setToBeansObject($address, $additionalData = []){
		/** @noinspection PhpUndefinedNamespaceInspection This method is only available if the BeansBooks module is installed. */
		if(!(
			$address instanceof \BeansBooks\Objects\CustomerAddress ||
			$address instanceof \BeansBooks\Objects\VendorAddress
		)){
			throw new Exception('Please only set an address to a BeansBooks CustomerAddress or VendorAddress.');
		}

		if($this->changed() || $this->get('beansbooks_id') == ''){
			// Something changed or it just doesn't exist in Beans, save it!
			// This is to save page execution time for saves that are saving something other than this field.
			$data = $additionalData;
			$keys = $this->getBeansKeys();

			foreach($keys as $rk => $lk){
				$data[$rk] = $this->get($lk);
			}

			$address->setFromArray($data);
			if($address->exists()){
				$address->update();
			}
			else{
				$address->create();
				$this->set('beansbooks_id', $address->get('id'));
			}
		}
	}
}