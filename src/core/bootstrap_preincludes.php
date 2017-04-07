<?php
/**
 * Core bootstrap helper file that includes all the necessary core files
 *
 * This file is the core of the application; it's responsible for setting up
 *  all the necessary paths, settings and includes.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
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

// These are generally files required for getting the rest of the system loadable.
require_once(ROOT_PDIR . "core/libs/core/ISingleton.interface.php");
//require_once("core/classes/IDatabaseClass.interface.php");
require_once(ROOT_PDIR . 'core/libs/core/XMLLoader.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/JSLibrary.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/SQLBuilder.class.php');
/** @deprecated 2011.11 */
require_once(ROOT_PDIR . 'core/libs/core/InstallArchive.class.php');
/** @deprecated 2011.11 */
require_once(ROOT_PDIR . 'core/libs/core/InstallArchiveAPI.class.php');


// The PHP elements of the MVC framework.
require_once(ROOT_PDIR . 'core/libs/core/Exceptions.php');

require_once(ROOT_PDIR . 'core/libs/core/date/Timezone.php');
require_once(ROOT_PDIR . 'core/libs/core/date/DateTime.php');

require_once(ROOT_PDIR . 'core/libs/core/datamodel/DMI.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Model.class.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/Schema.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn.php');

require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___created.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___deleted.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___id.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___id_fk.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___site.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___updated.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___uuid.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn___uuid_fk.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_boolean.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_data.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_enum.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_float.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_int.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_string.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_text.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_ISO_8601_date.php');
require_once(ROOT_PDIR . 'core/libs/core/datamodel/columns/SchemaColumn_ISO_8601_datetime.php');

require_once(ROOT_PDIR . 'core/libs/core/ModelSchema.php');

// Time is a useful component.
require_once(ROOT_PDIR . 'core/libs/core/Time.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Session.class.php');

require_once(ROOT_PDIR . 'core/models/ComponentModel.class.php');
require_once(ROOT_PDIR . 'core/models/PageModel.class.php');
require_once(ROOT_PDIR . 'core/models/SessionModel.class.php');
require_once(ROOT_PDIR . 'core/models/PageMetaModel.class.php');
require_once(ROOT_PDIR . 'core/models/Insertable.class.php');
require_once(ROOT_PDIR . 'core/models/SystemLogModel.php');
require_once(ROOT_PDIR . 'core/models/UserModel.php');
require_once(ROOT_PDIR . 'core/models/ConfigModel.class.php');
require_once(ROOT_PDIR . 'core/models/WidgetModel.class.php');
require_once(ROOT_PDIR . 'core/models/UserUserConfigModel.php');
require_once(ROOT_PDIR . 'core/models/UserConfigModel.php');
require_once(ROOT_PDIR . 'core/models/UserUserGroupModel.php');
require_once(ROOT_PDIR . 'core/models/UserGroupModel.php');

/**
 * The Component system written for API 2.1
 */
require_once(ROOT_PDIR . 'core/libs/core/VersionString.php');
require_once(ROOT_PDIR . 'core/libs/core/Component_2_1.php');


// File manipulation is a core feature required by the component system.
require_once(ROOT_PDIR . 'core/libs/core/filestore/functions.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/File.interface.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/Directory.interface.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/Factory.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/Directory_Backend.interface.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/FileContentFactory.class.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/Contents.interface.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/contents/ContentXML.php');
//require_once(ROOT_PDIR . 'core/libs/core/filestore/backends/file_awss3.backend.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/backends/FileLocal.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/ftp/FTPConnection.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/ftp/FTPMetaFile.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/backends/FileFTP.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/backends/FileRemote.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/backends/DirectoryLocal.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/backends/DirectoryFTP.php');

// Many of these are needed because some systems, such as the installer
// execute before the ComponentHandler has loaded the class locations.
require_once(ROOT_PDIR . 'core/libs/core/ComponentFactory.php');
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
// Include the caching system.
require_once(ROOT_PDIR . 'core/libs/core/Cache.php');
require_once(ROOT_PDIR . 'core/libs/core/cache/CacheInterface.php');
require_once(ROOT_PDIR . 'core/libs/core/cache/File.php');

require_once(ROOT_PDIR . 'core/libs/core/ViewControl.class.php');
require_once(ROOT_PDIR . 'core/libs/core/ViewMeta.class.php');
require_once(ROOT_PDIR . 'core/libs/core/errormanagement/functions.php');
require_once(ROOT_PDIR . 'core/functions/global.php');

// Load in the new integrated theme.
require_once(ROOT_PDIR . 'core/libs/core/theme/Theme.php');
