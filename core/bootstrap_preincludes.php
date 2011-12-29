<?php
/**
 * Core bootstrap helper file that includes all the necessary core files
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// These are generally files required for getting the rest of the system loadable.
require_once(ROOT_PDIR . 'core/libs/core/Debug.class.php');
require_once(ROOT_PDIR . "core/libs/core/ISingleton.interface.php");
//require_once("core/classes/IDatabaseClass.interface.php");
require_once(ROOT_PDIR . 'core/libs/core/XMLLoader.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/JSLibrary.class.php');
require_once(ROOT_PDIR . 'core/libs/core/SQLBuilder.class.php');
/** @deprecated 2011.11 */
require_once(ROOT_PDIR . 'core/libs/core/InstallArchive.class.php');
/** @deprecated 2011.11 */
require_once(ROOT_PDIR . 'core/libs/core/InstallArchiveAPI.class.php');
/** @deprecated 2011.11 */
require_once(ROOT_PDIR . 'core/libs/core/Component.class.php');
/**
 * The Component system written for API 2.1 
 */
require_once(ROOT_PDIR . 'core/libs/core/Component_2_1.php');
require_once(ROOT_PDIR . 'core/functions/Core.functions.php');

// File manipulation is a core feature required by the component system.
require_once(ROOT_PDIR . 'core/libs/filestore/File_Backend.interface.php');
require_once(ROOT_PDIR . 'core/libs/filestore/Directory_Backend.interface.php');
require_once(ROOT_PDIR . 'core/libs/filestore/FileContentFactory.class.php');
require_once(ROOT_PDIR . 'core/libs/filestore/backends/file_awss3.backend.php');
require_once(ROOT_PDIR . 'core/libs/filestore/backends/file_local.backend.php');
require_once(ROOT_PDIR . 'core/libs/filestore/backends/directory_local.backend.php');

// Many of these are needed because some systems, such as the installer
// execute before the ComponentHandler has loaded the class locations.
require_once(ROOT_PDIR . 'core/libs/core/ComponentFactory.php');
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/backends/icachecore.interface.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/backends/cachecore.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/backends/cachefile.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/Cache.class.php');

// The PHP elements of the MVC framework.
require_once(ROOT_PDIR . 'core/libs/core/Model.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Controller.class.php');

// Time is a useful component.
require_once(ROOT_PDIR . 'core/libs/core/Time.class.php');

require_once(ROOT_PDIR . 'core/models/ComponentModel.class.php');
require_once(ROOT_PDIR . 'core/models/PageModel.class.php');