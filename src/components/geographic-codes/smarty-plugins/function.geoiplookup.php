<?php
/**
 * @package Core
 * @since 1.9
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

	if(\Core\is_ip_private($ip)){
		$lookup = null;
		$country = 'LOCAL';
		$cname = 'Local/Internal Connection';
		$flag = 'assets/images/iso-country-flags/intl.png';
	}
	else{
		$lookup = new \geocode\IPLookup($ip);
		$country = $lookup->country;
		$cname = $lookup->getCountryName();
		$flag = 'assets/images/iso-country-flags/' . strtolower($lookup->country) . '.png';
	}

	if($getflag){
		$file = \Core\Filestore\Factory::File($flag);

		if($file->exists()){
			$out = '<img src="' . $file->getPreviewURL('20x20') . '" title="' . $cname . '" alt="' . $country . '"/> ';
		}
		else{
			$out = '';
		}
	}
	else{
		$out = '';
	}

	
	if($lookup && $lookup->province && $lookup->city){
		$out .= $lookup->city . ', ' . $lookup->province;	
	}
	elseif($lookup && $lookup->province){
		$out .= $lookup->province;
	}
	elseif($lookup && $lookup->city){
		$out .= $lookup->city;
	}
	elseif($country){
		$out .= $country;
	}

	return $out;
}