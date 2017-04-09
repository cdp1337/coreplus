<?php
/**
 * 
 *
 * @package Core
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


class PageMetaModel extends Model {
	public static $Schema = array(
		'site' => array(
			'type' => Model::ATT_TYPE_SITE,
			'default' => -1,
			'formtype' => 'system',
			'comment' => 'The site id in multisite mode, (or -1 if global)',
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
			'form' => array('type' => 'system'),
		),
		'meta_key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 24,
			'required' => true,
			'comment' => 'The key of this meta tag',
		),
		'meta_value' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'comment' => 'Machine version of the value of this meta tag',
		),
		'meta_value_title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'required' => true,
			'comment' => 'Human readable version of the value of this meta tag',
		),
	);

	public static $Indexes = array(
		'primary' => array('site', 'baseurl', 'meta_key', 'meta_value'),
	);

	public function  __construct() {
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_BELONGSTOONE,
				'on' => 'baseurl',
			),
		);

		// This system now has a combined primary key.
		// HOWEVER, construction of the model should still be allowed to be performed with simply the baseurl.
		// The first part of the key can be assumed.
		if(func_num_args() == 3){
			if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
				$site = MultiSiteHelper::GetCurrentSiteID();
			}
			else{
				$site = null;
			}
			$key1 = func_get_arg(0);
			$key2 = func_get_arg(1);
			$key3 = func_get_arg(2);
			parent::__construct($site, $key1, $key2, $key3);
			$this->load();
		}
		elseif(func_num_args() == 4){
			$site = func_get_arg(0);
			$key1 = func_get_arg(1);
			$key2 = func_get_arg(2);
			$key3 = func_get_arg(3);
			parent::__construct($site, $key1, $key2, $key3);
		}
		else{
			parent::__construct();
		}
	}

	/**
	 * Get the ViewMeta object relating to this specific meta type.
	 *
	 * @return ViewMeta
	 */
	public function getViewMetaObject(){
		$m = ViewMeta::Factory($this->get('meta_key'));

		$m->contentkey = $this->get('meta_value');
		$m->content = $this->get('meta_value_title');

		return $m;
	}
}
