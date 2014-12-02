<?php
/**
 * whois registration file for ro. TLD
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

/*
BUG
- date on ro could be given as "mail date" (ex: updated field)
- multiple person for one role, ex: news.ro
- seems the only role listed is registrant
*/

class ro_handler {
	function parse($data_str, $query) {
		$translate = [
			'fax-no'            => 'fax',
			'e-mail'            => 'email',
			'nic-hdl'           => 'handle',
			'person'            => 'name',
			'address'           => 'address.',
			'domain-name'       => '',
			'updated'           => 'changed',
			'registration-date' => 'created',
			'domain-status'     => 'status',
			'nameserver'        => 'nserver'
		];

		$contacts = [
			'admin-contact'     => 'admin',
			'technical-contact' => 'tech',
			'zone-contact'      => 'zone',
			'billing-contact'   => 'billing'
		];

		$extra = [
			'postal code:' => 'address.pcode'
		];

		$reg = \phpwhois\generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

		if(isset($reg['domain']['description'])) {
			$reg['owner'] = get_contact($reg['domain']['description'], $extra);
			unset($reg['domain']['description']);

			foreach($reg as $key => $item) {
				if(isset($item['address'])) {
					$data = $item['address'];
					unset($reg[ $key ]['address']);
					$reg[ $key ] = array_merge($reg[ $key ], get_contact($data, $extra));
				}
			}

			$reg['registered'] = 'yes';
		}
		else
			$reg['registered'] = 'no';

		$r['regrinfo'] = $reg;
		$r['regyinfo'] = [
			'referrer'  => 'http://www.nic.ro',
			'registrar' => 'nic.ro'
		];

		return $r;
	}
}
