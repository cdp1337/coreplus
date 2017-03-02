<?php
/**
 * Class file for FormDateInput
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

namespace Core\Forms;

/**
 * Class FormDateInput provides a jQuery date picker
 *
 * All the options from the official jQuery date picker API are supported,
 * simply pass them in as attributes.
 *
 * @see http://api.jqueryui.com/datepicker/
 * @package Core\Forms
 */
class DateInput extends TextInput {

	/**
	 * The javascript construction string that's used in the javascript.
	 * @var string
	 */
	public $_javascriptconstructorstring = '';

	public function render(){
		
		// The javascript mappings aren't 100%, as they're expecting specific capitalization.... argh
		$mappings = array(
			'altfield' => 'altField',
			'altformat' => 'altFormat',
			'appendtext' => 'appendText',
			'autosize' => 'autoSize',
			'buttonimage' => 'buttonImage',
			'buttonimageonly' => 'buttonImageOnly',
			'buttontext' => 'buttonText',
			'calculateweek' => 'calculateWeek',
			'changemonth' => 'changeMonth',
			'changeyear' => 'changeYear',
			'closetext' => 'closeText',
			'constraininput' => 'constrainInput',
			'currenttext' => 'currentText',
			'dateformat' => 'dateFormat',
			'daynames' => 'dayNames',
			'daynamesmin' => 'dayNamesMin',
			'daynamesshort' => 'dayNamesShort',
			'defaultdate' => 'defaultDate',
			'duration' => 'duration',
			'firstday' => 'firstDay',
			'gotocurrent' => 'gotoCurrent',
			'hideifnoprevnext' => 'hideIfNoPrevNext',
			'isrtl' => 'isRTL',
			'maxdate' => 'maxDate',
			'mindate' => 'minDate',
			'monthnames' => 'monthNames',
			'monthnamesshort' => 'monthNamesShort',
			'navigationasdateformat' => 'navigationAsDateFormat',
			'nexttext' => 'nextText',
			'numberofmonths' => 'numberOfMonths',
			'prevtext' => 'prevText',
			'selectothermonths' => 'selectOtherMonths',
			'shortyearcutoff' => 'shortYearCutoff',
			'showanim' => 'showAnim',
			'showbuttonpanel' => 'showButtonPanel',
			'showcurrentatpos' => 'showCurrentAtPos',
			'showmonthafteryear' => 'showMonthAfterYear',
			'showon' => 'showOn',
			'showoptions' => 'showOptions',
			'showothermonths' => 'showOtherMonths',
			'showweek' => 'showWeek',
			'stepmonths' => 'stepMonths',
			'weekheader' => 'weekHeader',
			'yearrange' => 'yearRange',
			'yearsuffix' => 'yearSuffix',
			'beforeshow' => 'beforeShow',
			'beforeshowday' => 'beforeShowDay',
			'onchangemonthyear' => 'onChangeMonthYear',
			'onclose' => 'onClose',
			'onselect' => 'onSelect',
		);

		// Go through and assign the javascript constructor string first.

		$opts = array();
		foreach($this->_attributes as $k => $v){
			// If this starts with datepicker_.... it's an argument!
			if(strpos($k, 'datepicker_') === 0){
				$key = substr($k, 11);
				// make sure it's in the list.
				if(isset($mappings[$key])){
					$opts[ $mappings[$key] ] = $v;
				}
			}
		}

		$this->_javascriptconstructorstring = json_encode($opts);

		// Does the value need to be transposed to a specific display format?
		if(isset($this->_attributes['displayformat'])){
			$v = $this->get('value');
			if($v === 0 || $v === '0'){
				$this->_attributes['value'] = '';
			}
			elseif(is_numeric($v)){
				$dt = new \Core\Date\DateTime($v);
				$formattedvalue = $dt->format($this->_attributes['displayformat']);
				$this->_attributes['value'] = $formattedvalue;
			}
		}

		return parent::render();
	}

	public function setValue($value){

		if($value === '' || $value === NULL){
			// Allow empty values to be entered without manipulation.
			// This is to prevent an empty value from tripping up the !is_numeric check below and
			// getting set to an empty date string, which evaluates to the current date/time.
			return parent::setValue($value);
		}
		elseif(isset($this->_attributes['saveformat']) && !is_numeric($value)){
			// Set value succeeded, now I can convert the string to an int, (if requested).
			$dt = new \Core\Date\DateTime($value);
			$value = $dt->format($this->_attributes['saveformat'], \Core\Date\Timezone::TIMEZONE_DEFAULT);
			return parent::setValue($value);
		}
		else{
			return parent::setValue($value);
		}
	}
}
