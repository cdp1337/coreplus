<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 2/7/13
 * Time: 2:53 AM
 * To change this template use File | Settings | File Templates.
 */
class FormDateInput extends FormTextInput {

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

		return parent::render();
	}
}
