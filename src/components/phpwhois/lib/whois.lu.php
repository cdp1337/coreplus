<?php
/**
 * whois registration file for lu. TLD
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

if(!defined('__LU_HANDLER__')) define('__LU_HANDLER__', 1);

require_once('whois.parser.php');

class lu_handler {
	function parse($data_str, $query) {
		$items = [
			'domainname:'  => 'domain.name',
			'domaintype:'  => 'domain.status',
			'nserver:'     => 'domain.nserver.',
			'registered:'  => 'domain.created',
			'source:'      => 'domain.source',
			'ownertype:'   => 'owner.type',
			'org-name:'    => 'owner.organization',
			'org-address:' => 'owner.address.',
			'org-zipcode:' => 'owner.address.pcode',
			'org-city:'    => 'owner.address.city',
			'org-country:' => 'owner.address.country',
			'adm-name:'    => 'admin.name',
			'adm-address:' => 'admin.address.',
			'adm-zipcode:' => 'admin.address.pcode',
			'adm-city:'    => 'admin.address.city',
			'adm-country:' => 'admin.address.country',
			'adm-email:'   => 'admin.email',
			'tec-name:'    => 'tech.name',
			'tec-address:' => 'tech.address.',
			'tec-zipcode:' => 'tech.address.pcode',
			'tec-city:'    => 'tech.address.city',
			'tec-country:' => 'tech.address.country',
			'tec-email:'   => 'tech.email',
			'bil-name:'    => 'billing.name',
			'bil-address:' => 'billing.address.',
			'bil-zipcode:' => 'billing.address.pcode',
			'bil-city:'    => 'billing.address.city',
			'bil-country:' => 'billing.address.country',
			'bil-email:'   => 'billing.email'
		];

		$r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'dmy');

		$r['regyinfo'] = [
			'referrer'  => 'http://www.dns.lu',
			'registrar' => 'DNS-LU'
		];

		return $r;
	}
}
