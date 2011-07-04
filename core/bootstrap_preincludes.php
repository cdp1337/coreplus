<?php
/**
 * Core bootstrap helper file that includes all the necessary core files
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

// These are generally files required for getting the rest of the system loadable.
require_once(ROOT_PDIR . 'core/libs/core/Debug.class.php');
require_once(ROOT_PDIR . "core/libs/core/ISingleton.interface.php");
//require_once("core/classes/IDatabaseClass.interface.php");
require_once(ROOT_PDIR . 'core/libs/core/XMLLoader.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/JSLibrary.class.php');
require_once(ROOT_PDIR . 'core/libs/core/SQLBuilder.class.php');
require_once(ROOT_PDIR . 'core/libs/core/InstallArchive.class.php');
require_once(ROOT_PDIR . 'core/libs/core/InstallArchiveAPI.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Component.class.php');

// File manipulation is a core feature required by the component system.
require_once(ROOT_PDIR . 'core/libs/file-abstraction/File_Backend.interface.php');
require_once(ROOT_PDIR . 'core/libs/file-abstraction/backends/awss3.backend.php');
require_once(ROOT_PDIR . 'core/libs/file-abstraction/backends/local.backend.php');

// Many of these are needed because some systems, such as the installer
// execute before the ComponentHandler has loaded the class locations.
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

require_once(ROOT_PDIR . 'core/libs/core/ComponentModel.class.php');