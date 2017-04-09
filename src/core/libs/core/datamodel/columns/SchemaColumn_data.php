<?php
/**
 * File for class Schema definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1655
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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