<?php
/**
 * whois registration file for nl. TLD
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

if(!defined('__NL_HANDLER__')) define('__NL_HANDLER__', 1);

require_once('whois.parser.php');

class nl_handler {
	function parse($data, $query) {
		$items = [
			'domain.name'    => 'Domain name:',
			'domain.status'  => 'Status:',
			'domain.nserver' => 'Domain nameservers:',
			'domain.created' => 'Date registered:',
			'domain.changed' => 'Record last updated:',
			'domain.sponsor' => 'Registrar:',
			'admin'          => 'Administrative contact:',
			'tech'           => 'Technical contact(s):'
		];

		$r['regrinfo']              = get_blocks($data['rawdata'], $items);
		$r['regyinfo']['referrer']  = 'http://www.domain-registry.nl';
		$r['regyinfo']['registrar'] = 'Stichting Internet Domeinregistratie NL';

		if(!isset($r['regrinfo']['domain']['status'])) {
			$r['regrinfo']['registered'] = 'no';

			return $r;
		}

		if(isset($r['regrinfo']['tech'])) $r['regrinfo']['tech'] = $this->get_contact($r['regrinfo']['tech']);

		if(isset($r['regrinfo']['zone'])) $r['regrinfo']['zone'] = $this->get_contact($r['regrinfo']['zone']);

		if(isset($r['regrinfo']['admin'])) $r['regrinfo']['admin'] = $this->get_contact($r['regrinfo']['admin']);

		if(isset($r['regrinfo']['owner'])) $r['regrinfo']['owner'] = $this->get_contact($r['regrinfo']['owner']);

		$r['regrinfo']['registered'] = 'yes';
		format_dates($r, 'dmy');

		return $r;
	}

	function get_contact($data) {
		$r = get_contact($data);

		if(isset($r['name']) && preg_match('/^[A-Z0-9]+-[A-Z0-9]+$/', $r['name'])) {
			$r['handle'] = $r['name'];
			$r['name']   = array_shift($r['address']);
		}

		return $r;
	}
}
