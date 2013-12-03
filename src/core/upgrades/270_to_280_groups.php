<?php
/**
 * Upgrade script to convert all the JSON-encoded user groups and transpose them to the new UserUserGroup object.
 */

$dataset = \Core\Datamodel\Dataset::Init()
	->select(['id', 'groups'])
	->table('user')
	->execute();

var_dump($dataset); die();


// datamodel backed users have the groups listed in their column "groups".
$g = json_decode($this->_getModel()->get('groups'), true);
if(!$g) $g = array();

// Failover, if the user's group is a flat array of integers and multisite mode is enabled,
// convert that to an array of sites, 0 being the first (root), with that list of groups.
// This is useful for a site that has existing users that then converts to multisite.
if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
	if(isset($g[0]) && !is_array($g[0])){

		$g = array(0 => $g);
		$this->_getModel()->set('groups', json_encode($g));
		// It'll get saved on save() (if called).
	}
}

$this->_groups = $g;