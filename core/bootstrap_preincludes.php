<?php

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

// Many of these are needed because some systems, such as the installer
// execute before the ComponentHandler has loaded the class locations.
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/backends/icachecore.interface.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/backends/cachecore.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/backends/cachefile.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/Cache.class.php');

// Time is a useful component.
require_once(ROOT_PDIR . 'core/libs/core/Time.class.php');

// The PHP elements of the MVC framework.
require_once(ROOT_PDIR . 'core/libs/core/Model.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Controller.class.php');