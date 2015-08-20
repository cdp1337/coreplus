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
 * Primary method for a block of user-customizable content inside a template.
 *
 * Insertables are the core method of injecting blocks of user-customizable content into a template.
 *
 * An insertable must be on a template that has a registered page URL, as the baseurl is what is tracked as one of the main primary keys.
 * The other PK is the insertable's name, which must be unique on that one template.
 *
 * #### Smarty Parameters
 *
 *  * name
 *    * The key name of this input value, must be present and unique on this template.
 *  * assign
 *    * Assign the value instead of outputting to the screen.
 *  * title
 *    * When editing the insertable, the title displayed along side the input field.
 *  * type
 *
 * #### Example Usage
 *
 * <pre>
 * {insertable name="body" title="Body Content"}
 * <p>
 * This is some example content!
 * </p>
 * {/insertable}
 * </pre>
 *
 * <pre>
 * {insertable name="img1" title="Large Image" assign="img1"}
 * {img src="`$img1`" placeholder="generic" dimensions="800x400"}
 * {/insertable}
 * </pre>
 *
 * @param array       $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param string|null $content Null on opening pass, rendered source of the contents inside the block on closing pass
 * @param Smarty      $smarty  Parent Smarty template object
 * @param boolean     $repeat  True at the first call of the block-function (the opening tag) and
 * false on all subsequent calls to the block function (the block's closing tag).
 * Each time the function implementation returns with $repeat being TRUE,
 * the contents between {func}...{/func} are evaluated and the function implementation
 * is called again with the new block contents in the parameter $content.
 *
 * @return string
 */
function smarty_block_insertable($params, $content, $smarty, &$repeat){

	$assign = (isset($params['assign']))? $params['assign'] : false;

	// This only needs to be called once.
	// If a value is being assigned, then it's on the first pass so the value will be assigned by the time the content is hit.
	if($assign){
		if($repeat){
			// Running the first time with an assign variable, OK!
		}
		else{
			return $content;
		}
	}
	else{
		// No assign requested, run on the second only.
		if($repeat){
			return '';
		}
		else{
			// Continue!
		}
	}

	$page = PageRequest::GetSystemRequest()->getPageModel();

	// I need to use the parent to lookup the current base url.
	$baseurl = PageRequest::GetSystemRequest()->getBaseURL();

	if(!isset($params['name'])) return '';

	$i = InsertableModel::Construct($page->get('site'), $baseurl, $params['name']);

	if($i->exists()){
		$value = $i->get('value');
	}
	else{
		$value = $content;
	}

	if(isset($params['type']) && $params['type'] == 'markdown'){
		// Convert this markdown code to HTML via the built-in Michielf library.
		$value = Core\MarkdownProcessor::defaultTransform($value);
		//$value = Michelf\MarkdownExtra::defaultTransform($value);
	}
	else{
		// Coreify the string
		$value = \Core\parse_html($value);
	}

	if($assign){
		$smarty->assign($assign, $value);
	}
	else{
		return $value;
	}
}