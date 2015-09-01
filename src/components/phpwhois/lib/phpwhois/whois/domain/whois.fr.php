<?php
/**
 * whois registration file for fr. TLD
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

class fr_handler {
	function parse($data_str, $query) {
		$translate = [
			'fax-no'      => 'fax',
			'e-mail'      => 'email',
			'nic-hdl'     => 'handle',
			'ns-list'     => 'handle',
			'person'      => 'name',
			'address'     => 'address.',
			'descr'       => 'desc',
			'anniversary' => '',
			'domain'      => '',
			'last-update' => 'changed',
			'registered'  => 'created',
			'country'     => 'address.country',
			'registrar'   => 'sponsor',
			'role'        => 'organization'
		];

		$contacts = [
			'admin-c'  => 'admin',
			'tech-c'   => 'tech',
			'zone-c'   => 'zone',
			'holder-c' => 'owner',
			'nsl-id'   => 'nserver'
		];

		$reg = \phpwhois\generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'dmY');

		if(isset($reg['nserver'])) {
			$reg['domain'] = array_merge($reg['domain'], $reg['nserver']);
			unset($reg['nserver']);
		}

		$r['regrinfo'] = $reg;
		$r['regyinfo'] = [
			'referrer'  => 'http://www.nic.fr',
			'registrar' => 'AFNIC'
		];

		return $r;
	}
}
