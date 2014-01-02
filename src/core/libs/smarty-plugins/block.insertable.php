<?php
/**
 * @package Core Plus\Core
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

function smarty_block_insertable($params, $content, $template, &$repeat){

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

	// Coreify the string
	$value = \Core\parse_html($value);

	if($assign){
		$template->assign($assign, $value);
	}
	else{
		return $value;
	}
}