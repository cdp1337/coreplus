<?php
/**
 * @package Core Plus\Core
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

	// This only needs to be called once.
	if($repeat) return '';

	// I need to use the parent to lookup the current base url.
	$baseurl = $template->parent->getBaseURL();

	if(!isset($params['name'])) return '';
	$assign = (isset($params['assign']))? $params['assign'] : false;

	$i = InsertableModel::Construct($baseurl, $params['name']);

	if($i->exists()){
		$content = $i->get('value');
	}

    return $assign ? $template->assign($assign, $content) : $content;
}