<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 8/6/16
 * Time: 10:40 PM
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