<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * Copyright (C) 2010  Charlie Powell <charlie@eval.bz>
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
 *
 * @package [packagename]
 * @author Charlie Powell <charlie@eval.bz>
 * @date [date]
 */

function smarty_block_insertable($params, $content, $template, &$repeat){
	// I need to use the parent to lookup the current base url.
	$baseurl = $template->parent->getBaseURL();

	if(!isset($params['name'])) return '';
	$assign = (isset($params['assign']))? $params['assign'] : false;

	$i = new InsertableModel($baseurl, $params['name']);

	if($i->exists()){
		$content = $i->get('value');
	}

    return $assign ? $template->assign($assign, $content) : $content;
}