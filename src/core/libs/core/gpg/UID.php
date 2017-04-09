<?php
/**
 * File for class KeyUID definition in the coreplus project
 * 
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.2056
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

namespace Core\GPG;


/**
 * A short teaser of what KeyUID does.
 *
 * More lengthy description of what KeyUID does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for KeyUID
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
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class UID extends Key {
	/**
	 * @var string The full name of this UID
	 */
	public $fullname;

	/** @var string Comment of this UID */
	public $comment;

	/**
	 * @var string The email attached to this UID
	 */
	public $email;

	/** @var array Signatures attached to this public key */
	public $sigs = [];
	
	public function _parseSig($parts){
		
		$sig = new UIDSig();

		switch($parts[3]){
			case '1':
				$sig->encryptionType = Key::ENCRYPTION_TYPE_RSA;
				break;
			case '16':
				$sig->encryptionType = Key::ENCRYPTION_TYPE_ELGAMAL;
				break;
			case '17':
				$sig->encryptionType = Key::ENCRYPTION_TYPE_DSA;
				break;
			case '20':
				$sig->encryptionType = Key::ENCRYPTION_TYPE_ELGAMAL;
				break;
		}
		$sig->id = $parts[4];
		$sig->id_short = substr($parts[4], -8);
		$sig->created = $parts[5];

		$split = GPG::ParseAuthorString($parts[9]);
		$sig->fullname = $split['name'];
		$sig->email    = $split['email'];
		$sig->comment  = $split['comment'];
		
		if($parts[10] != ''){
			$code = substr($parts[10], 0, 2);
			switch($code){
				case 10:
					$sig->certification = UIDSig::CERTIFY_NONE;
					break;
				case 11:
					$sig->certification = UIDSig::CERTIFY_PERSONA;
					break;
				case 12:
					$sig->certification = UIDSig::CERTIFY_CASUAL;
					break;
				case 13:
				case 18:
					$sig->certification = UIDSig::CERTIFY_EXTENSIVE;
					break;
			}
		}
		
		$this->sigs[] = $sig;
	}
	
	public function getTrustLevel(){
		$gpg = new GPG();
		
		// Revoked UIDs are easy!
		if($this->validity == 'r'){
			return -1;
		}
		
		if($this->expires && $this->expires < time()){
			return -2;
		}
		
		switch($this->validity){
			case '-':
				$trust = 0;
				break;
			case 'e':
				$trust = -3;
				break;
			case 'q':
				$trust = 0;
				break;
			case 'n':
				$trust = -999;
				break;
			case 'm':
				$trust = 1;
				break;
			case 'f':
				$trust = 2;
				break;
			case 'u':
				$trust = 2;
				break;
			default:
				$trust = 0;
		}
		
		// Whoami?
		// This is based off the private keys that are installed locally.
		$secs = $gpg->listSecretKeys();
		$me = [];
		
		// Make this a flat array.
		foreach($secs as $s){
			$me[] = $s->id;
		}
		
		foreach($this->sigs as $sig){
			if(in_array($sig->id, $me)){
				$trust = 2;
			}
		}
		
		return $trust;
	}
} 