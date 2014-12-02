<?php
/**
 * whois registration file for mx. TLD
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

class mx_handler {
	function parse($data_str, $query) {
		$items = [
			'owner'          => 'Registrant:',
			'admin'          => 'Administrative Contact:',
			'tech'           => 'Technical Contact:',
			'billing'        => 'Billing Contact:',
			'domain.nserver' => 'Name Servers:',
			'domain.created' => 'Created On:',
			'domain.expires' => 'Expiration Date:',
			'domain.changed' => 'Last Updated On:',
			'domain.sponsor' => 'Registrar:'
		];

		$extra = [
			'city:'  => 'address.city',
			'state:' => 'address.state',
			'dns:'   => '0'
		];

		$r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'dmy', $extra);

		$r['regyinfo'] = [
			'registrar' => 'NIC Mexico',
			'referrer'  => 'http://www.nic.mx/'
		];

		if(empty($r['regrinfo']['domain']['created'])) $r['regrinfo']['registered'] = 'no';
		else
			$r['regrinfo']['registered'] = 'yes';

		return $r;
	}
}
