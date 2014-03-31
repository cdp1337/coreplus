<?php
/**
 * @package Core
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

/**
 * @param $params
 * @param $template
 *
 * @return string
 * @throws SmartyException
 */
function smarty_function_geoiplookup($params, $template){

	$ip = $params[0];
	$getflag = isset($params['flag']) ? $params['flag'] : true;

	$lookup = new \geocode\IPLookup($ip);

	if($getflag){
		$flag = 'assets/images/iso-country-flags/png-country-4x3/res-640x480/' . strtolower($lookup->country) . '.png';
		$file = \Core\Filestore\Factory::File($flag);

		if($file->exists()){
			$out = '<img src="' . $file->getPreviewURL('20x20') . '" title="' . $lookup->country . '"/> ';
		}
		else{
			$out = '';
		}
	}
	else{
		$out = '';
	}

	$out .= $lookup->city . ', ' . $lookup->province;

	return $out;
}