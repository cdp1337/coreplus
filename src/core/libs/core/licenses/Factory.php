<?php
/**
 * File for class Factory definition in the coreplus project
 * 
 * @package Core\Licenses
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130716.1334
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

namespace Core\Licenses;


/**
 * A short teaser of what Factory does.
 *
 * More lengthy description of what Factory does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Factory
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
 * @package Core\Licenses
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Factory {
	/**
	 * Get the available licenses as an array pre-formatted for use as options.
	 *
	 * @return array
	 */
	public static function GetAsOptions() {
		$licenses = self::GetLicenses();

		$licopts = ['' => '-- Select a License (if available) --'];
		foreach($licenses as $name => $dat){
			$licopts[$name] = $dat['opttitle'];
		}

		return $licopts;

	}

	/**
	 * Get a specific license by its key, title, URL, ?or alias?
	 *
	 * @param string $key
	 *
	 * @return null|array
	 */
	public static function GetLicense($key) {
		$all = self::GetLicenses();
		$match = null;
		
		if(isset($all[ $key ])){
			// EASY!
			$match = $key;
		}
		
		// Otherwise I need to search for it.
		if(!$match){
			foreach($all as $k => $dat){
				if($dat['title'] == $key){
					$match = $k;
					break;
				}
				if($dat['url'] == $key){
					$match = $k;
					break;
				}
				if(isset($dat['aliases'])){
					foreach($dat['aliases'] as $a){
						if($a == $key){
							$match = $k;
							break 2;
						}
					}
				}
			}	
		}
		
		return $match ? array_merge($all[ $match ], ['key' => $match]) : null;
	}

	/**
	 * Try to detect a license based on its contents.
	 * 
	 * @param string $contents The full contents of the license to autodetect from.
	 * 
	 * @return array|null
	 */
	public static function DetectLicense($contents) {
		// Load in just 2kb of the file, that should be enough to detect the license.
		$explodedContents = array_map('trim', explode("\n", substr($contents, 0, 2048)));
		$lics = self::GetLicenses();

		$lic = null;
		foreach($lics as $key => $ldat){
			if(isset($ldat['matches'])){
				$g = true;
				foreach($ldat['matches'] as $lm){
					if(!in_array($lm, $explodedContents)){
						$g = false;
						break;
					}
				}

				if($g){
					// All lines match!
					return array_merge($ldat, ['key' => $key ]);
				}
			}
		}
		
		// no?
		return null;
	}


	/**
	 * Get the entire list of licenses registered in Core.
	 *
	 * @return array
	 */
	public static function GetLicenses() {
		return array(
			// BSD
			'bsd-3-clause' => [
				'title'    => '3-Clause BSD License',
				'opttitle' => '[BSD] Modified BSD License',
				'url'      => 'https://opensource.org/licenses/BSD-3-Clause',
				'matches' => [
					'Redistributions of source code must retain the above copyright notice',
					'Redistributions in binary form must reproduce the above copyright notice',
					'Neither the name of the copyright holder nor the names of its contributors may be used to endorse',
				],
				'aliases' => [
					'New BSD License',
					'Modified BSD License',
				]
			],
			'bsd-2-clause' => [
				'title'    => '2-Clause BSD License',
				'opttitle' => '[BSD] Simplified BSD License',
				'url'      => 'https://opensource.org/licenses/BSD-2-Clause',
				'matches' => [
					'Redistributions of source code must retain the above copyright notice',
					'Redistributions in binary form must reproduce the above copyright notice',
				],
				'aliases' => [
					'Simplified BSD License',
					'FreeBSD License',
				]
			],
			
			// Creative Commons - Attribution
			'cc-by-3.0' => [
				'title'    => 'Creative Commons Attribution',
				'opttitle' => '[CC BY v3] Creative Commons - Attribution 3.0',
				'url'      => 'http://creativecommons.org/licenses/by/3.0/',
			],
			'cc-by-2.0' => [
				'title'    => 'Creative Commons Attribution',
				'opttitle' => '[CC BY v2] Creative Commons - Attribution 2.0',
				'url'      => 'http://creativecommons.org/licenses/by/2.0/',
			],

			// Creative Commons - Attribution-ShareAlike
			'cc-by-sa-3.0' => [
				'title'    => 'Creative Commons Attribution-ShareAlike',
				'opttitle' => '[CC BY-SA v3] Creative Commons - Attribution-ShareAlike 3.0',
				'url'      => 'http://creativecommons.org/licenses/by-sa/3.0/',
				'aliases' => [
					'CCAttribution-ShareAlike 3.0'
				],
			],
			'cc-by-sa-2.5' => [
				'title'    => 'Creative Commons Attribution-ShareAlike',
				'opttitle' => '[CC BY-SA v2] Creative Commons - Attribution-ShareAlike 2.5',
				'url'      => 'http://creativecommons.org/licenses/by-sa/2.5/',
				'aliases' => [
					'CCAttribution-ShareAlike 2.5'
				],
			],
			'cc-by-sa-2.0' => [
				'title'    => 'Creative Commons Attribution-ShareAlike',
				'opttitle' => '[CC BY-SA v2] Creative Commons - Attribution-ShareAlike 2.0',
				'url'      => 'http://creativecommons.org/licenses/by-sa/2.0/',
				'aliases' => [
					'CCAttribution-ShareAlike 2.0'
				],
			],

			// Creative Commons - Attribution-NoDerivs
			'cc-by-nd-3.0' => [
				'title'    => 'Creative Commons Attribution-NoDerivs',
				'opttitle' => '[CC BY-ND v3] Creative Commons - Attribution-NoDerivs 3.0',
				'url'      => 'http://creativecommons.org/licenses/by-nd/3.0/',
			],
			'cc-by-nd-2.0' => [
				'title'    => 'Creative Commons Attribution-NoDerivs',
				'opttitle' => '[CC BY-ND v2] Creative Commons - Attribution-NoDerivs 2.0',
				'url'      => 'http://creativecommons.org/licenses/by-nd/2.0/',
			],

			// Creative Commons - Attribution-NonCommercial
			'cc-by-nc-3.0' => [
				'title'    => 'Creative Commons Attribution-NonCommercial',
				'opttitle' => '[CC BY-NC v3] Creative Commons - Attribution-NonCommercial 3.0',
				'url'      => 'http://creativecommons.org/licenses/by-nc/3.0/',
			],
			'cc-by-nc-2.0' => [
				'title'    => 'Creative Commons Attribution-NonCommercial',
				'opttitle' => '[CC BY-NC v2] Creative Commons - Attribution-NonCommercial 2.0',
				'url'      => 'http://creativecommons.org/licenses/by-nc/2.0/',
			],

			// Creative Commons - Attribution-NonCommercial-ShareAlike
			'cc-by-nc-sa-3.0' => [
				'title'    => 'Creative Commons Attribution-NonCommercial-ShareAlike',
				'opttitle' => '[CC BY-NC-SA v3] Creative Commons - Att.-NonComm.-ShareAlike 3.0',
				'url'      => 'http://creativecommons.org/licenses/by-nc-sa/3.0/',
			],
			'cc-by-nc-sa-2.0' => [
				'title'    => 'Creative Commons Attribution-NonCommercial-ShareAlike',
				'opttitle' => '[CC BY-NC-SA v2] Creative Commons - Att.-NonComm.-ShareAlike 2.0',
				'url'      => 'http://creativecommons.org/licenses/by-nc-sa/2.0/',
			],

			// Creative Commons - Attribution-NonCommercial-NoDerivs
			'cc-by-nc-nd-3.0' => [
				'title'    => 'Creative Commons Attribution-NonCommercial-NoDerivs',
				'opttitle' => '[CC BY-NC-ND v3] Creative Commons - Att.-NonComm.-NoDerivs 3.0',
				'url'      => 'http://creativecommons.org/licenses/by-nc-nd/3.0/',
			],
			'cc-by-nc-nd-2.0' => [
				'title'    => 'Creative Commons Attribution-NonCommercial-NoDerivs',
				'opttitle' => '[CC BY-NC-ND v2] Creative Commons - Att.-NonComm.-NoDerivs 2.0',
				'url'      => 'http://creativecommons.org/licenses/by-nc-nd/2.0/',
			],

			// GNU GPL
			'gnu-gpl-3' => [
				'title'    => 'GNU General Public License',
				'opttitle' => '[GNU GPLv3] GNU - General Public License v3',
				'url'      => 'http://www.gnu.org/licenses/gpl-3.0.html',
				'matches' => [
					'GNU GENERAL PUBLIC LICENSE',
					'Version 3, 29 June 2007',
				],
				'aliases' => [
					'GPLv3',
				],
			],
			'gnu-gpl-2' => [
				'title'    => 'GNU General Public License',
				'opttitle' => '[GNU GPLv2] GNU - General Public License v2',
				'url'      => 'http://www.gnu.org/licenses/gpl-2.0.html',
				'matches' => [
					'GNU GENERAL PUBLIC LICENSE',
					'Version 2, June 1991',
				],
				'aliases' => [
					'GPLv2',
				],
			],
			'gnu-gpl-1' => [
				'title'    => 'GNU General Public License',
				'opttitle' => '[GNU GPLv1] GNU - General Public License v1',
				'url'      => 'http://www.gnu.org/licenses/gpl-1.0.html',
				'matches' => [
					'GNU GENERAL PUBLIC LICENSE',
					'Version 1, February 1989',
				]
			],

			// GNU LGPL
			'gnu-lgpl-3' => [
				'title'    => 'GNU Lesser General Public License',
				'opttitle' => '[GNU LGPLv3] GNU - Lesser General Public License v3',
				'url'      => 'http://www.gnu.org/licenses/lgpl-3.0.html',
				'matches' => [
					'GNU LESSER GENERAL PUBLIC LICENSE',
					'Version 3, 29 June 2007',
				]
			],
			'gnu-lgpl-2.1' => [
				'title'    => 'GNU Lesser General Public License',
				'opttitle' => '[GNU LGPLv2.1] GNU - Lesser General Public License v2.1',
				'url'      => 'http://www.gnu.org/licenses/lgpl-2.1.html',
				'matches' => [
					'GNU LESSER GENERAL PUBLIC LICENSE',
					'Version 2.1, February 1999',
				],
				'aliases' => [
					'GNU Library or "Lesser" General Public License version 2.1',
				]
			],

			// GNU AGPL
			'gnu-agpl-3' => [
				'title' => 'GNU Affero General Public License',
				'opttitle' => '[GNU AGPLv3] GNU - Affero General Public License v3',
				'url' => 'http://www.gnu.org/licenses/agpl-3.0.html',
				'matches' => [
					'"This License" refers to version 3 of the GNU Affero General Public License.',
				],
				'aliases' => [
					'http://www.gnu.org/licenses/agpl-3.0.txt',
					'GNU Affero General Public License v3',
				],
			],

			// GNU FDL
			'gnu-fdl-1.3' => [
				'title'    => 'GNU Free Documentation License',
				'opttitle' => '[GNU FDLv1.3] GNU - Free Documentation License v1.3',
				'url'      => 'http://www.gnu.org/licenses/fdl-1.3.html',
			],
			'gnu-fdl-1.2' => [
				'title'    => 'GNU Free Documentation License',
				'opttitle' => '[GNU FDLv1.2] GNU - Free Documentation License v1.2',
				'url'      => 'http://www.gnu.org/licenses/fdl-1.2.html',
			],
			'gnu-fdl-1.1' => [
				'title'    => 'GNU Free Documentation License',
				'opttitle' => '[GNU FDLv1.1] GNU - Free Documentation License v1.1',
				'url'      => 'http://www.gnu.org/licenses/fdl-1.1.html',
			],

			// Apache
			'apache-2.0' => [
				'title'    => 'Apache License',
				'opttitle' => 'Apache License, Version 2.0',
				'url'      => 'http://www.apache.org/licenses/LICENSE-2.0',
				'matches' => [
					'Apache License',
					'Version 2.0, January 2004',
					'http://www.apache.org/licenses/',
				],
				'aliases' => [
					'APL',
				],
			],

			// Mozilla license
			'moz-mpl-2.0' => [
				'title'    => 'Mozilla Public License',
				'opttitle' => '[MPL] Mozilla Public License, Version 2.0',
				'url'      => 'http://www.mozilla.org/MPL/2.0/',
				'aliases' => [
					'MPL',
				],
			],

			// MIT
			'mit' => [
				'title'    => 'MIT License',
				'opttitle' => '[MIT] MIT License',
				'url'      => 'http://opensource.org/licenses/MIT',
				'matches' => [
					'Permission is hereby granted, free of charge, to any person obtaining a copy',
					'in the Software without restriction, including without limitation the rights',
					'The above copyright notice and this permission notice shall be included in',
					'all copies or substantial portions of the Software.',
					'THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR',
					'IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,',
				],
				'aliases' => [
					'MIT',
				]
			],
			
			// The DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE!
			[
				'title' => 'WTFPL',
				'opttitle' => 'DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE',
				'url' => 'http://www.wtfpl.net/',
				'matches' => [
					'DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE'
				]
			]
		);
	}
}