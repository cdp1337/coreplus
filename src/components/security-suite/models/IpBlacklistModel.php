<?php
/**
 * File for class IpBlacklistModel definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130423.0120
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
 * A short teaser of what IpBlacklistModel does.
 *
 * More lengthy description of what IpBlacklistModel does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for IpBlacklistModel
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class IpBlacklistModel extends Model{
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_UUID,
		),
		'ip_addr'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
			'form' => array(
				'title' => 'IP Address',
				'description' => 'The IP address to block, be careful with this ;)<br/>This accepts a CIDR notation, (Just end it with "/32" if you do not know what that means)',
			),
		),
		'expires' => array(
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'Expire date, set to "0" to never expire',
			'form' => array(
				'type' => 'date',
				'description' => 'The date that this ban expires, leave blank or set to 0 to permaban.'
			)
		),
		'message' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'comment' => 'An optional message to the user should this IP try again.',
			'form' => array(
				'description' => 'An optional message to display to the user from this IP address.'
			)
		),
		'comment' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'comment' => 'An optional administrative message',
			'form' => array(
				'description' => 'An optional administrative message that is displayed along with the ban in the listing.'
			)
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
		'unique:ip_addr' => array('ip_addr'),
	);

	/**
	 * @param string $k
	 * @param mixed  $v
	 *
	 * @return bool|void
	 * @throws ModelValidationException
	 */
	public function set($k, $v){
		if($k == 'ip_addr'){
			return $this->setIpAddr($v);
		}
		else{
			return parent::set($k, $v);
		}
	}

	/**
	 * Set the IP along with translation and validation.
	 *
	 * @param $ip
	 * @return bool
	 * @throws ModelValidationException
	 */
	public function setIpAddr($ip){
		if(strpos($ip, '/') !== false){
			list($ip, $cidr) = explode('/', $ip);
		}
		else{
			// Allow a single IP to be passed in, it'll get set as a single host.
			$cidr = 32;
		}

		if($cidr > 32){
			throw new ModelValidationException('Invalid CIDR value for IP address, must be 32 or below!');
		}
		if($cidr < 8){
			throw new ModelValidationException('Invalid CIDR value for IP address, must be 8 or above!');
		}

		// This will combine the IP back together with the correct network mask and all!
		$longip = ip2long($ip);
		$mask = ~((1 << (32 - $cidr)) - 1);
		$join = long2ip($longip & $mask) . '/' . $cidr;

		return parent::set('ip_addr', $join);
	}
}