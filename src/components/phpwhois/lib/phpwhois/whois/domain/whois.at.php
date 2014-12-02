<?php
/**
 * whois registration file for at. TLD
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

class at_handler {
	function parse($data_str, $query) {
		$translate = [
			'fax-no'         => 'fax',
			'e-mail'         => 'email',
			'nic-hdl'        => 'handle',
			'person'         => 'name',
			'personname'     => 'name',
			'street address' => 'address.street',
			'city'           => 'address.city',
			'postal code'    => 'address.pcode',
			'country'        => 'address.country'
		];

		$contacts = [
			'registrant' => 'owner',
			'admin-c'    => 'admin',
			'tech-c'     => 'tech',
			'billing-c'  => 'billing',
			'zone-c'     => 'zone'
		];

		$reg = \phpwhois\generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

		if(isset($reg['domain']['remarks'])) unset($reg['domain']['remarks']);

		if(isset($reg['domain']['descr'])) {
			while(list($key, $val) = each($reg['domain']['descr'])) {
				$v = trim(substr(strstr($val, ':'), 1));
				if(strstr($val, '[organization]:')) {
					$reg['owner']['organization'] = $v;
					continue;
				}
				if(strstr($val, '[phone]:')) {
					$reg['owner']['phone'] = $v;
					continue;
				}
				if(strstr($val, '[fax-no]:')) {
					$reg['owner']['fax'] = $v;
					continue;
				}
				if(strstr($val, '[e-mail]:')) {
					$reg['owner']['email'] = $v;
					continue;
				}

				$reg['owner']['address'][ $key ] = $v;
			}

			if(isset($reg['domain']['descr'])) unset($reg['domain']['descr']);
		}

		$r['regrinfo'] = $reg;
		$r['regyinfo'] = [
			'referrer'  => 'http://www.nic.at',
			'registrar' => 'NIC-AT'
		];

		return $r;
	}
}

?>
