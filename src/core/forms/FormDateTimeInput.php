<?php
/**
 * Class file for FormDateTimeInput
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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
 * Class FormDateTimeInput provides a jQuery datepicker with time added on.
 *
 * <h3>Parameters</h3>
 *
 * <p>
 * All the options from the <a href="http://api.jqueryui.com/datepicker/" target="_BLANK">official jQuery date picker API</a>
 * are supported, simply pass them in as attributes.
 * </p>
 *
 * <p>
 * Besides the jquery options, the datetime picker also supports the parameters "displayformat" and "saveformat".
 * </p>
 *
 * <h4>displayformat</h4>
 * <p>
 * The "displayformat" option controls how a timestamp is formatted to display on the form.
 * This string should match the <a href="http://php.net/manual/en/function.date.php" target="_BLANK">format provided at php.net</a>.
 * </p>
 *
 * <p>
 * This timestamp is converted from GMT to the user's default timezone automatically.
 * </p>
 *
 * <h4>saveformat</h4>
 * <p>
 * Generally used alongside "displayformat".  Useful for taking a human-readable string and converting that back to a machine timestamp.
 * Set this to "U" to achieve this.
 * </p>
 *
 * <p>
 * This string is converted from the user's default timezone to GMT automatically.
 * </p>
 *
 * @link http://api.jqueryui.com/datepicker/
 * @package Core\Forms
 */
class FormDateTimeInput extends FormTextInput {

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
			'timeformat' => 'timeFormat',
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
			if(strpos($k, 'datetimepicker_') === 0){
				$key = substr($k, 15);
				// make sure it's in the list.
				if(isset($mappings[$key])){
					$opts[ $mappings[$key] ] = $v;
				}
			}
		}

		$this->_javascriptconstructorstring = json_encode($opts);

		// Does the value need to be transposed to a specific display format?
		if(isset($this->_attributes['displayformat'])){
			if(is_numeric($this->get('value'))){
				$dt = new CoreDateTime($this->get('value'));
				$formattedvalue = $dt->getFormatted($this->_attributes['displayformat']);
				$this->_attributes['value'] = $formattedvalue;
			}
		}

		return parent::render();
	}

	public function setValue($value){
		if(isset($this->_attributes['saveformat']) && !is_numeric($value)){
			// Set value succeeded, now I can convert the string to an int, (if requested).
			$dt = new CoreDateTime($value);
			$value = $dt->getFormatted($this->_attributes['saveformat'], Time::TIMEZONE_GMT);
			return parent::setValue($value);
		}
		else{
			return parent::setValue($value);
		}
	}
}
