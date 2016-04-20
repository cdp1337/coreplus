<?php
/**
 * Upgrade file for migrating user data from the user user configs to the new supplemental user table.
 */

// The migrations for source (user_user_config) to destination (user).
$migrations = [
	'/user/gpgauth/pubkey' => 'gpgauth_pubkey',
	'json:profiles' => 'external_profiles',
];

// Lookup each key and the corresponding user attached.
$sources = \Core\Datamodel\Dataset::Init()
	->select(['user_id', 'key', 'value'])
	->table('user_user_config')
	->where('key IN ' . implode(',', array_keys($migrations)))
	->executeAndGet();

$users = [];

foreach($sources as $s){
	if(!isset($users[ $s['user_id'] ])){
		$users[ $s['user_id'] ] = UserModel::Construct($s['user_id']);
	}
	
	/** @var UserModel $u */
	$u = $users[ $s['user_id'] ];
	$nk = $migrations[ $s['key'] ];
	$nv = $s['value'];
	
	$u->set($nk, $nv);
}

// Now save each user loaded into the array.
foreach($users as $u){
	/** @var UserModel $u */
	$u->save();
}
