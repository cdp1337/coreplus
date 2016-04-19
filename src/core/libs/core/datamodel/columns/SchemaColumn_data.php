<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn_data extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_DATA;
	}

	/**
	 * Set the value from the database for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromDB($val){
		$this->valueDB = $val;
		$this->value = $val;
		
		if($this->encrypted){
			// Decrypt the value first.
			$val = \Model::DecryptValue($val);
		}

		if($this->encoding == \Model::ATT_ENCODING_JSON){
			// If there is an encoding on this key, perform whatever encoding operation requested.
			if($val === '' || $val === null){
				// $v is a blank value, this doesn't JSON_DECODE very well, so simply return the default version.
				$this->valueTranslated = $this->null ? null : '';
			}
			else{
				// DECODE!
				$this->valueTranslated = json_decode($val, true);
			}
		}
		elseif($this->encoding == \Model::ATT_ENCODING_GZIP){
			if($val === '' || $val === null){
				// $v is a blank value, this doesn't JSON_DECODE very well, so simply return the default version.
				$this->valueTranslated = $this->null ? null : '';
			}
			else{
				// DECODE!
				$this->valueTranslated = gzuncompress($val);
				if($this->valueTranslated === false){
					// GZ-uncompression failed for some reason.
					$this->valueTranslated = $this->null ? null : '';
				}
			}
		}
		else{
			// No changes necessary.
			$this->valueTranslated = $val;
		}
	}

	/**
	 * Set the value from the application/userspace for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromApp($val){
		$this->valueTranslated = $val;

		if($this->encoding == \Model::ATT_ENCODING_JSON){
			$val = json_encode($val);
		}
		elseif($this->encoding == \Model::ATT_ENCODING_GZIP){
			$val = gzcompress($val);
		}
		
		if($this->encrypted){
			// Encrypt the value last
			$val = \Model::EncryptValue($val);
		}

		$this->value = $val;
	}
}