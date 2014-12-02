<?php
/**
 * whois registration file for bh. TLD
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

if(!defined('__BH_HANDLER__')) define('__BH_HANDLER__', 1);

require_once('whois.parser.php');

class bh_handler {
	function parse($data_str, $query) {
		$items         = [
			'Sponsoring Registrar Name:'  => 'domain.sponsor.name',
			'Sponsoring Registrar Email:' => 'domain.sponsor.email',
			'Sponsoring Registrar Uri:'   => 'domain.sponsor.uri',
			'Sponsoring Registrar Phone:' => 'domain.sponsor.phone'
		];
		$i             = generic_parser_b($data_str['rawdata'], $items);
		$r['regrinfo'] = generic_parser_b($data_str['rawdata']);
		if(isset($r['regrinfo']['domain']) && is_array($r['regrinfo']['domain'])) $r['regrinfo']['domain']['sponsor'] =
			$i['domain']['sponsor'];
		if(empty($r['regrinfo']['domain']['created'])) $r['regrinfo']['registered'] = 'no';
		else
			$r['regrinfo']['registered'] = 'yes';
		$r['regyinfo'] = [
			'referrer'  => 'http://www.nic.bh/',
			'registrar' => 'NIC-BH'
		];

		return $r;
	}
}
