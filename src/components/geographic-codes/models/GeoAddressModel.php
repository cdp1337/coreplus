<?php
/**
 * Class file for the model GeoAddressModel
 * 
 * Will automatically try to geocode the result to get the most updated lat/lng.
 * If this is undesirable, set "__skip_geocode" to true and that process will be skipped completely.
 *
 * @package Geographic Codes
 * @author Charlie Powell <charlie@evalagency.com>
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
		'model' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 96,
			'required' => false,
			'comment' => 'The model name this address represents (optional)',
		),
		'record_key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => false,
			'default' => '',
			'comment' => 'The record primary key this address represents (optional)',
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
		'primary' => ['id'],
		'idx_record' => ['model', 'record_key'],
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

	public function save($defer = false){
		// Quick check to see if this address is actually populated or not.
		if( $this->get('address1') == '' && $this->get('postal') == '' ){
			if($this->exists()){
				$ret = $this->delete();
				$this->set('id', null);
				return $ret;
			}
			else{
				// No change!
				return false;
			}
		}
		elseif($this->changed()){

			if(Core::IsLibraryAvailable('GoogleMaps') && !$this->get('__skip_geocoding')){
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
			return parent::save($defer);
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
}