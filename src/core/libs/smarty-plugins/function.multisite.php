<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
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
 * @param array $params Parameters sent from templates
 * @param Smarty $smarty Smarty object
 */
function smarty_function_multisite($params, $smarty){
	
	if(!Core::IsComponentAvailable('multisite')){
		return 'Multisite mode not available';
	}
	
	// The site ID is expected to be the first parameter.
	if(!isset($params[0])){
		throw new SmartyException('Please set the site ID as the first parameter for multisite.');
	}
	$siteid = $params[0];
	
	if($siteid == 0){
		$html = 'ROOT SITE';
		$link = '<a href="' . \Core\resolve_link('site:0/') . '" target="_blank">' .
			'(' . ConfigHandler::Get('/multisite/rooturl') . ')' .
			'</a>';
	}
	elseif($siteid == -1){
		$html = 'GLOBAL (all sites)';
		$link = '';
	}
	else{
		// Lookup this site so I can get the full data for it.
		$site = MultiSiteModel::Construct($siteid);

		if(!$site->exists()){
			$html = 'Site ' . $siteid . ' not found!';
			$link = '';
		}
		else{
			// The site exists!  Set the resulting HTML to have the text of the site along with a link to view it.
			$html = $site->get('name');

			// If this site active, then also include the link.
			if($site->get('status') == 'active'){
				$link = '<a href="' . \Core\resolve_link('site:' . $site->get('id') . '/') . '" target="_blank">' .
					'(' . $site->get('url') . ')' .
					'</a>';
			}
			else{
				$link = '<i class="icon-lock" title="Site is not active"></i>';
			}
		}
	}
	
	// Should the link be conditional?
	$html .= ' ' . $link;
	
	if(isset($params['assign'])){
		$smarty->assign($params['assign'], $html);
	}
	else{
		return $html;
	}
}