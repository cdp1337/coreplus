<?php
/**
 * whois registration file for coop. TLD
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

namespace phpwhois;

if(!defined('__COOP_HANDLER__')) define('__COOP_HANDLER__', 1);

require_once('whois.parser.php');

class coop_handler {
	function parse($data_str, $query) {

		$items = [
			'owner'           => 'Contact Type:            registrant',
			'admin'           => 'Contact Type:            admin',
			'tech'            => 'Contact Type:            tech',
			'billing'         => 'Contact Type:            billing',
			'domain.name'     => 'Domain Name:',
			'domain.handle'   => 'Domain ID:',
			'domain.expires'  => 'Expiry Date:',
			'domain.created'  => 'Created:',
			'domain.changed'  => 'Last updated:',
			'domain.status'   => 'Domain Status:',
			'domain.sponsor'  => 'Sponsoring registrar:',
			'domain.nserver.' => 'Host Name:'
		];

		$translate = [
			'Contact ID:'     => 'handle',
			'Name:'           => 'name',
			'Organisation:'   => 'organization',
			'Street 1:'       => 'address.street.0',
			'Street 2:'       => 'address.street.1',
			'Street 3:'       => 'address.street.2',
			'City:'           => 'address.city',
			'State/Province:' => 'address.state',
			'Postal code:'    => 'address.pcode',
			'Country:'        => 'address.country',
			'Voice:'          => 'phone',
			'Fax:'            => 'fax',
			'Email:'          => 'email'
		];

		$blocks = get_blocks($data_str['rawdata'], $items);

		$r = [];

		if(isset($blocks['domain'])) {
			$r['regrinfo']['domain']     = format_dates($blocks['domain'], 'dmy');
			$r['regrinfo']['registered'] = 'yes';

			if(isset($blocks['owner'])) {
				$r['regrinfo']['owner'] = generic_parser_b($blocks['owner'], $translate, 'dmy', false);

				if(isset($blocks['tech'])) $r['regrinfo']['tech'] =
					generic_parser_b($blocks['tech'], $translate, 'dmy', false);

				if(isset($blocks['admin'])) $r['regrinfo']['admin'] =
					generic_parser_b($blocks['admin'], $translate, 'dmy', false);

				if(isset($blocks['billing'])) $r['regrinfo']['billing'] =
					generic_parser_b($blocks['billing'], $translate, 'dmy', false);
			}
			else {
				$r['regrinfo']['owner'] = generic_parser_b($data_str['rawdata'], $translate, 'dmy', false);
			}
		}
		else
			$r['regrinfo']['registered'] = 'no';

		$r['regyinfo'] = [
			'referrer'  => 'http://www.nic.coop',
			'registrar' => '.coop registry'
		];

		return $r;
	}
}
