<?php
/**
 * whois registration file for org. TLD
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

class org_handler {
	function parse($data_str, $query) {
		$r['regrinfo'] = \phpwhois\generic_parser_b($data_str['rawdata']);

		if(!strncmp($data_str['rawdata'][0], 'WHOIS LIMIT EXCEEDED', 20)) $r['regrinfo']['registered'] = 'unknown';

		$r['regyinfo']['referrer']  = 'http://www.pir.org/';
		$r['regyinfo']['registrar'] = 'Public Interest Registry';

		return $r;
	}
}
