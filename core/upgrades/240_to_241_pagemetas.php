<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 1/23/13
 * Time: 2:29 PM
 *
 * Upgrade file from 2.4.0 to 2.4.1 to handle converting legacy meta data to the new format.
 */


// Give me the page system!
require_once(ROOT_PDIR . 'core/models/PageModel.class.php');
require_once(ROOT_PDIR . 'core/models/PageMetaModel.class.php');

// Get every page that currently exists
$pages = PageModel::FindRaw();
foreach($pages as $page){
	/** @var $page array */

	// No meta data here?  No need to do anything!
	if(!$page['metas']) continue;

	// Try to decode it, since it's json encoded.
	$metadata = json_decode($page['metas'], true);

	// Can't decode it?  No biggie :p
	if(!$metadata) continue;

	foreach($metadata as $tag => $value){
		if($tag == 'author'){
			if(isset($metadata['authorid'])){
				$machinevalue = $metadata['authorid'];
			}
			else{
				$machinevalue = '';
			}

			if(!$value) continue;

			$meta = new PageMetaModel($page['site'], $page['baseurl'], 'author', $machinevalue);
			$meta->set('meta_value_title', $value);
			$meta->save();
		}
		elseif($tag == 'authorid'){
			// This field is ignored, it's handled in the author case.
			continue;
		}
		elseif($tag == 'keywords'){
			// This field is actually an array of entries, separated by a comma.
			$values = array_map('trim', explode(',', $value));

			foreach($values as $tag){
				if(!$tag) continue;

				$machinevalue = \Core\str_to_url($tag);
				$meta = new PageMetaModel($page['site'], $page['baseurl'], 'keyword', $machinevalue);
				$meta->set('meta_value_title', $tag);
				$meta->save();
			}
		}
		else{
			// Everything else...
			if(!$value) continue;

			$meta = new PageMetaModel($page['site'], $page['baseurl'], strtolower($tag), '');
			// These don't have values set, just the value title, (which is the human readable version).
			$meta->set('meta_value_title', $value);
			$meta->save();
		}
	}
}