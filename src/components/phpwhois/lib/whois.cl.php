<?php
/**
 * whois registration file for cl. TLD
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

if(!defined('__CL_HANDLER__')) define('__CL_HANDLER__', 1);

require_once('whois.parser.php');

class cl_handler {
	function parse($data_str, $query) {
		$items = [
			'admin'          => '(Administrative Contact)',
			'tech'           => 'Contacto Técnico (Technical Contact):',
			'domain.nserver' => 'Servidores de nombre (Domain servers):',
			'domain.changed' => '(Database last updated on):'
		];

		$trans = [
			'organización:' => 'organization',
			'nombre      :' => 'name'
		];

		$r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'd-m-y', $trans);
		$r['regyinfo'] = [
			'referrer'  => 'http://www.nic.cl',
			'registrar' => 'NIC Chile'
		];

		return $r;
	}
}

?>
