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
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'hidden',
			'null' => true,
			'comment' => 'Latitude of this location',
		),
		'lng' => array(
			'type' => Model::ATT_TYPE_STRING,
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
		else{
			// Resume with the traditional save!
			return parent::save();
		}
	}
}