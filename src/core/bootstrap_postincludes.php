<?php
/**
 * All the post includes, these are here for performance reasons, (they can get compiled into the compiled bootstrap)
 */

// These are needed for smarty
if(!defined('SMARTY_DIR')){
	define('SMARTY_DIR', ROOT_PDIR . 'core/libs/smarty/');
}
//require_once(ROOT_PDIR . 'core/libs/smarty/sysplugins/smarty_internal_data.php');
require_once(ROOT_PDIR . 'core/libs/smarty/Smarty.class.php');
//require_once(ROOT_PDIR . 'core/libs/smarty/sysplugins/smarty_internal_templatebase.php');
//require_once(ROOT_PDIR . 'core/libs/smarty/sysplugins/smarty_internal_template.php');
//require_once(ROOT_PDIR . 'core/libs/smarty/sysplugins/smarty_resource.php');
//require_once(ROOT_PDIR . 'core/libs/smarty/sysplugins/smarty_internal_resource_file.php');
//require_once(ROOT_PDIR . 'core/libs/smarty/sysplugins/smarty_cacheresource.php');
//require_once(ROOT_PDIR . 'core/libs/smarty/sysplugins/smarty_internal_cacheresource_file.php');
require_once(ROOT_PDIR . 'core/libs/core/CurrentPage.class.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Template.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Exception.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/TemplateInterface.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/backends/Smarty.php');
require_once(ROOT_PDIR . 'core/libs/core/UserAgent.php');
require_once(ROOT_PDIR . 'core/libs/core/View.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Widget_2_1.class.php');
//require_once(ROOT_PDIR . 'core/functions/Core.functions.php');
//require_once(ROOT_PDIR . 'core/libs/datamodel/backends/mysqli.backend.php');
require_once(ROOT_PDIR . 'core/libs/core/Form.class.php');
//require_once(ROOT_PDIR . 'core/models/SessionModel.class.php');

require_once(ROOT_PDIR . 'core/libs/core/PageRequest.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Controller_2_1.class.php');
require_once(ROOT_PDIR . 'core/models/WidgetModel.class.php');
require_once(ROOT_PDIR . 'core/libs/core/CoreDateTime.php');