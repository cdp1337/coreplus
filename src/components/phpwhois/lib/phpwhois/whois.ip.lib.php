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

namespace phpwhois;

	//-----------------------------------------------------------------
// Check if ip adddress is valid



//-----------------------------------------------------------------
// Get real client ip address



//-----------------------------------------------------------------
// Convert from CIDR to net range

function cidr_conv($net) {
	$start = strtok($net, '/');
	$n     = 3 - substr_count($net, '.');

	if($n > 0) {
		for($i = $n; $i > 0; $i--) {
			$start .= '.0';
		}
	}

	$bits1 = str_pad(decbin(ip2long($start)), 32, '0', 'STR_PAD_LEFT');
	$net   = pow(2, (32 - substr(strstr($net, '/'), 1))) - 1;
	$bits2 = str_pad(decbin($net), 32, '0', 'STR_PAD_LEFT');
	$final = '';

	for($i = 0; $i < 32; $i++) {
		if($bits1[ $i ] == $bits2[ $i ]) $final .= $bits1[ $i ];
		if($bits1[ $i ] == 1 and $bits2[ $i ] == 0) $final .= $bits1[ $i ];
		if($bits1[ $i ] == 0 and $bits2[ $i ] == 1) $final .= $bits2[ $i ];
	}

	return $start . " - " . long2ip(bindec($final));
}
