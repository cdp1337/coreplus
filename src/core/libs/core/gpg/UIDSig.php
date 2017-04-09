<?php
/**
 * File for class Key definition in the coreplus project
 * 
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.1923
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


class UIDSig extends Key {
	
	const CERTIFY_NONE = 0;
	const CERTIFY_PERSONA = 1;
	const CERTIFY_CASUAL = 2;
	const CERTIFY_EXTENSIVE = 3;
	
	/** @var string The full name of this UID */
	public $fullname;

	/** @var string The email attached to this UID */
	public $email;
	
	public $certification;
	
	// sig:::1:52ACB7D9A31370C4:1443849459::::Michael (Max) Wilson (Professional) <max.elwyn@gmail.com>:10x:::::8:
}