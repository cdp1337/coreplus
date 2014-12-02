<?php
/**
 * whois registration file for be. TLD
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

class be_handler {
	function parse($data, $query) {
		$items = [
			'domain.name'    => 'Domain:',
			'domain.status'  => 'Status:',
			'domain.nserver' => 'Nameservers:',
			'domain.created' => 'Registered:',
			'owner'          => 'Licensee:',
			'admin'          => 'Onsite Contacts:',
			'tech'           => 'Registrar Technical Contacts:',
			'agent'          => 'Registrar:',
			'agent.uri'      => 'Website:'
		];

		$trans = [
			'company name2:' => ''
		];

		$r['regrinfo'] = \phpwhois\get_blocks($data['rawdata'], $items);

		if($r['regrinfo']['domain']['status'] == 'REGISTERED') {
			$r['regrinfo']['registered'] = 'yes';
			$r['regrinfo']               = \phpwhois\get_contacts($r['regrinfo'], $trans);

			if(isset($r['regrinfo']['agent'])) {
				$sponsor = get_contact($r['regrinfo']['agent'], $trans);
				unset($r['regrinfo']['agent']);
				$r['regrinfo']['domain']['sponsor'] = $sponsor;
			}

			$r = \phpwhois\format_dates($r, '-mdy');
		}
		else
			$r['regrinfo']['registered'] = 'no';

		$r['regyinfo']['referrer']  = 'http://www.domain-registry.nl';
		$r['regyinfo']['registrar'] = 'DNS Belgium';

		return $r;
	}
}
