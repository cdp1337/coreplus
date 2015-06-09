<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * Render and add a form element to both the PHP session and HTML output.
 *
 * @experimental
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string
 * @throws SmartyException
 */
function smarty_function_add_form_element($params, $smarty){

	if(!isset($params['form'])){
		throw new SmartyException('{add_form_element} requires a form attribute!');
	}

	if($params['form'] instanceof Form){
		$form = $params['form'];
	}
	elseif($params['form'] instanceof \Core\ListingTable\Table){
		$form = $params['form']->getEditForm();
	}
	else{
		throw new SmartyException('Unsupported value provided for "form", please ensure it is a valid Form object!');
	}

	$type = isset($params['type']) ? $params['type'] : 'text';

	$element = FormElement::Factory($type, $params);

	$form->addElement($element);

	// Assign or render?
	if(isset($params['assign'])){
		$smarty->assign($params['assign'], $element->render());
	}
	else{
		echo $element->render();
	}
}