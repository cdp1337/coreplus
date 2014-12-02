<?php
/*
Whois.php        PHP classes to conduct whois queries

Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic

Maintained by David Saez

For the most recent version of this package visit:

http://www.phpwhois.org

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace phpwhois\whois\gtld;

class enom_handler {
	function parse($data_str, $query) {
		$items = [
			'owner.name'            => 'Registrant Name:',
			'owner.organization'    => 'Registrant Organization:',
			'owner.address.street'  => 'Registrant Street:',
			'owner.address.city'    => 'Registrant City:',
			'owner.address.state'   => 'Registrant State/Province:',
			'owner.address.pcode'   => 'Registrant Postal Code:',
			'owner.address.country' => 'Registrant Country:',
			'owner.phone'           => 'Registrant Phone:',
			'owner.fax'             => 'Registrant Fax:',
			'owner.email'           => 'Registrant Email:',

			'admin.name'            => 'Admin Name:',
			'admin.organization'    => 'Admin Organization:',
			'admin.address.street'  => 'Admin Street:',
			'admin.address.city'    => 'Admin City:',
			'admin.address.state'   => 'Admin State/Province:',
			'admin.address.pcode'   => 'Admin Postal Code:',
			'admin.address.country' => 'Admin Country:',
			'admin.phone'           => 'Admin Phone:',
			'admin.fax'             => 'Admin Fax:',
			'admin.email'           => 'Admin Email:',

			'tech.name'             => 'Tech Name:',
			'tech.organization'     => 'Tech Organization:',
			'tech.address.street'   => 'Tech Street:',
			'tech.address.city'     => 'Tech City:',
			'tech.address.state'    => 'Tech State/Province:',
			'tech.address.pcode'    => 'Tech Postal Code:',
			'tech.address.country'  => 'Tech Country:',
			'tech.phone'            => 'Tech Phone:',
			'tech.fax'              => 'Tech Fax:',
			'tech.email'            => 'Tech Email:',

			'domain.name'           => 'Domain Name:',
			'domain.sponsor'        => 'Reseller:',
			'domain.nserver'        => 'Name Server:',
			'domain.created'        => 'Creation Date:',
			'domain.expires'        => 'Registrar Registration Expiration Date:',
			'domain.changed'        => 'Updated Date:',
		    'domain.dnssrc'         => 'DNSSRC:',
		];

		//return easy_parser($data_str, $items, 'dmy', false, false, true);
		$r = \phpwhois\get_blocks($data_str, $items, false, false);
		\phpwhois\format_dates($r, 'dmy');
		return $r;
	}
}
