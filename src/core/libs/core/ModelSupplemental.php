<?php
/**
 * The Supplemental Model base interface.
 * 
 * Provides all placeholder methods that extending classes can utilize.
 * 
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @since 5.0.1
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

interface ModelSupplemental {

	/**
	 * Called prior to save completion.
	 * 
	 * @param Model $model The base model that is being saved
	 *
	 * @return void
	 */
	public static function PreSaveHook($model);

	/**
	 * Called immediately after the model has been saved to the database.
	 * 
	 * @param Model $model
	 *
	 * @return void
	 */
	public static function PostSaveHook($model);

	/**
	 * Called before the model is deleted from the database.
	 * 
	 * @param Model $model
	 *
	 * @return void
	 */
	public static function PreDeleteHook($model);

	/**
	 * Called during getControlLinks to return additional links in the controls.
	 * 
	 * @param Model $model
	 *
	 * @return array
	 */
	public static function GetControlLinks($model);
}
