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
}