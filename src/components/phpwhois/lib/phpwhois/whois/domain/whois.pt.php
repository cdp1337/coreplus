<?php
/**
 * whois registration file for pt. TLD
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

/* TODO:
   - whois - converter para http://domaininfo.com/idn_conversion.asp punnycode antes de efectuar a pesquisa
   - o punnycode deveria fazer parte dos resultados fazer parte dos resultados!
*/

class pt_handler {
	function parse($data, $query) {
		$items = [
			'domain.name'     => ' / Domain Name:',
			'domain.created'  => 'Data de registo / Creation Date (dd/mm/yyyy):',
			'domain.nserver.' => 'Nameserver:',
			'domain.status'   => 'Estado / Status:',
			'owner'           => 'Titular / Registrant',
			'billing'         => 'Entidade Gestora / Billing Contact',
			'admin'           => 'Respons�vel Administrativo / Admin Contact',
			'tech'            => 'Respons�vel T�cnico / Tech Contact',
			'#'               => 'Nameserver Information'
		];

		$r['regrinfo'] = \phpwhois\get_blocks($data['rawdata'], $items);

		if(empty($r['regrinfo']['domain']['name'])) {
			print_r($r['regrinfo']);
			$r['regrinfo']['registered'] = 'no';

			return $r;
		}

		$r['regrinfo']['domain']['created'] = get_date($r['regrinfo']['domain']['created'], 'dmy');

		if($r['regrinfo']['domain']['status'] == 'ACTIVE') {
			$r['regrinfo']               = \phpwhois\get_contacts($r['regrinfo']);
			$r['regrinfo']['registered'] = 'yes';
		}
		else
			$r['regrinfo']['registered'] = 'no';

		$r['regyinfo'] = [
			'referrer'  => 'http://www.fccn.pt',
			'registrar' => 'FCCN'
		];

		return $r;
	}
}
