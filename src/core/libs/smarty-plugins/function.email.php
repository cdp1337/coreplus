<?php
/**
 * @package Core\Templates\Smarty
 * @since 2.3.0
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

/**
 * Encode an email address in a way that makes it difficult for standard bots to read.
 *
 * @todo Finish documentation of smarty_function_email
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @throws SmartyException
 *
 * @return string
 */
function smarty_function_email($params, $smarty){

	if(isset($params['address']) && $params['address']){
		$email = $params['address'];
	}
	elseif(isset($params['email']) && $params['email']){
		$email = $params['email'];
	}
	elseif(isset($params[0]) && $params[0]){
		$email = $params[0];
	}
	else{
		return '{email} Unable to encode email addresses if no email is provided!';
	}

	\Core\view()->addScript('assets/js/core.strings.js');
	\Core\view()->addScript('assets/js/core.email.js');

	$id = 'e' . \Core\random_hex(5);
	$user = str_rot13(substr($email, 0, strpos($email, '@')));
	$tld = substr($email, strrpos($email, '.')+1);

	$atts = [
		'href' => '#',
		'id' => $id,
		'data-user' => $user,
		'data-tld' => $tld,
		'data-domain' => substr($email, strlen($user) + 1, -1-strlen($tld)),
	];

	$html = '';
	foreach($atts as $k => $v){
		$html .= ' ' . $k . '="' . $v . '"';
	}
	$html = '<a' . $html . '>#</a>';

	$html .= '<script type="text/javascript">Core.Email.Assemble("' . $id . '");</script>';
	return $html;
}
