<?php
/**
 * whois registration file for nu. TLD
 *
 * @package phpwhois
 *
 * @copyright 1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @author David Saez
 * @link http://www.phpwhois.org Original version of phpwhois
 *
 * @author Dmitry Lukashin <http://lukashin.ru/en/>
 * @link http://phpwhois.pw/ Revisited version of phpwhois
 *
 * @author Charlie Powell
 *
 * @license GPLv2
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace phpwhois\whois\domain;

class nu_handler {
	function parse($data_str, $query) {
		$items = [
			'name'    => 'Domain Name (UTF-8):',
			'created' => 'Record created on',
			'expires' => 'Record expires on',
			'changed' => 'Record last updated on',
			'status'  => 'Record status:',
			'handle'  => 'Record ID:'
		];

		while(list($key, $val) = each($data_str['rawdata'])) {
			$val = trim($val);

			if($val != '') {
				if($val == 'Domain servers in listed order:') {
					while(list($key, $val) = each($data_str['rawdata'])) {
						$val = trim($val);
						if($val == '') break;
						$r['regrinfo']['domain']['nserver'][] = $val;
					}
					break;
				}

				reset($items);

				while(list($field, $match) = each($items)) if(strstr($val, $match)) {
					$r['regrinfo']['domain'][ $field ] = trim(substr($val, strlen($match)));
					break;
				}
			}
		}

		if(isset($r['regrinfo']['domain'])) $r['regrinfo']['registered'] = 'yes';
		else
			$r['regrinfo']['registered'] = 'no';

		$r['regyinfo'] = [
			'whois'     => 'whois.nic.nu',
			'referrer'  => 'http://www.nunames.nu',
			'registrar' => '.NU Domain, Ltd'
		];

		\phpwhois\format_dates($r, 'dmy');

		return $r;
	}
}
