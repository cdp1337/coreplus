<?php
/**
 * whois registration file for br. TLD
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

require_once('whois.parser.php');

if(!defined('__BR_HANDLER__')) define('__BR_HANDLER__', 1);

class br_handler {

	function parse($data_str, $query) {
		$translate = [
			'fax-no'     => 'fax',
			'e-mail'     => 'email',
			'nic-hdl-br' => 'handle',
			'person'     => 'name',
			'netname'    => 'name',
			'domain'     => 'name',
			'updated'    => ''
		];

		$contacts = [
			'owner-c'   => 'owner',
			'tech-c'    => 'tech',
			'admin-c'   => 'admin',
			'billing-c' => 'billing'
		];

		$r = generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

		if(in_array('Permission denied.', $r['disclaimer'])) {
			$r['registered'] = 'unknown';

			return $r;
		}

		if(isset($r['domain']['nsstat'])) unset($r['domain']['nsstat']);
		if(isset($r['domain']['nslastaa'])) unset($r['domain']['nslastaa']);

		if(isset($r['domain']['owner'])) {
			$r['owner']['organization'] = $r['domain']['owner'];
			unset($r['domain']['owner']);
		}

		if(isset($r['domain']['responsible'])) unset($r['domain']['responsible']);
		if(isset($r['domain']['address'])) unset($r['domain']['address']);
		if(isset($r['domain']['phone'])) unset($r['domain']['phone']);

		$a['regrinfo'] = $r;
		$a['regyinfo'] = [
			'registrar' => 'BR-NIC',
			'referrer'  => 'http://www.nic.br'
		];

		return $a;
	}
}

?>
