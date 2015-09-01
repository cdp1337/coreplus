<?php
/**
 * whois registration file for eu. TLD
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

class eu_handler {
	function parse($data, $query) {

		$items = [
			'domain.name'      => 'Domain:',
			'domain.status'    => 'Status:',
			'domain.nserver'   => 'Name servers:',
			'domain.created'   => 'Registered:',
			'domain.registrar' => 'Registrar:',
			'tech'             => 'Registrar Technical Contacts:',
			'owner'            => 'Registrant:',
			''                 => 'Please visit'
		];

		$extra = [
			'organisation:' => 'organization',
			'website:'      => 'url'
		];

		$r['regrinfo'] = \phpwhois\get_blocks($data['rawdata'], $items);

		if(!empty($r['regrinfo']['domain']['status'])) switch($r['regrinfo']['domain']['status']) {
			case 'FREE':
			case 'AVAILABLE':
				$r['regrinfo']['registered'] = 'no';
				break;

			case 'APPLICATION PENDING':
				$r['regrinfo']['registered'] = 'pending';
				break;

			default:
				$r['regrinfo']['registered'] = 'unknown';
		}
		else
			$r['regrinfo']['registered'] = 'yes';

		if(isset($r['regrinfo']['tech'])) $r['regrinfo']['tech'] = get_contact($r['regrinfo']['tech'], $extra);

		if(isset($r['regrinfo']['domain']['registrar'])) $r['regrinfo']['domain']['registrar'] =
			get_contact($r['regrinfo']['domain']['registrar'], $extra);

		$r['regyinfo']['referrer']  = 'http://www.eurid.eu';
		$r['regyinfo']['registrar'] = 'EURID';

		return $r;
	}
}
