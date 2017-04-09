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


class SchemaColumn_boolean extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_BOOL;
		$this->encoding = \Model::ATT_ENCODING_UTF8;
		$this->default = '0';
		$this->formAttributes['type'] = 'radio';
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

		$this->valueTranslated = ($val == '0' || $val == '') ? false : true;
	}

	/**
	 * Set the value from the application/userspace for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromApp($val){
		// Convert yes-ish values to TRUE and no-ish values to FALSE.
		if(
			$val === '0' || $val === 0 || 
			$val === '' || 
			$val === 'no' || $val === 'NO'
		){
			$val = false;
		}
		elseif(
			$val == '1' || $val === 1 || 
			$val === 'yes' || $val === 'YES' || 
			$val === 'on' || $val === 'ON' ||
			$val === 'true' || $val === 'TRUE'
		){
			$val = true;
		}
		
		$this->valueTranslated = $val;

		$this->value = $val ? '1' : '0';
	}

	/**
	 * Get an array of the form element attributes for this column.
	 *
	 * @return array
	 */
	public function getFormElementAttributes(){
		$na = parent::getFormElementAttributes();

		// Allow options to be set automatically if not otherwise set by the Model.
		if(!isset($na['options'])){
			$na['options'] = ['yes' => 't:STRING_YES', 'no' => 't:STRING_NO'];
		}
		
		// The application is expecting true/false, but those don't translate too well to html :p
		if($this->valueTranslated === null){
			$na['value'] = null;
		}
		else{
			$na['value'] = $this->valueTranslated ? 'yes' : 'no';	
		}

		return $na;
	}
}