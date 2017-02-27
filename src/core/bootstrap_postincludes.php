<?php
/**
 * All the post includes, these are here for performance reasons, (they can get compiled into the compiled bootstrap)
 */

// These are needed for smarty
if(!defined('SMARTY_DIR')){
	define('SMARTY_DIR', ROOT_PDIR . 'core/libs/smarty/');
}
require_once(ROOT_PDIR . 'core/libs/smarty/Smarty.class.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Template.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Exception.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/TemplateInterface.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/backends/Smarty.php');
require_once(ROOT_PDIR . 'core/libs/core/UserAgent.php');
require_once(ROOT_PDIR . 'core/libs/core/View.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Widget.php');
require_once(ROOT_PDIR . 'core/libs/core/forms/FormGroup.php');
require_once(ROOT_PDIR . 'core/libs/core/forms/Form.php');

require_once(ROOT_PDIR . 'core/libs/core/PageRequest.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Controller_2_1.class.php');
require_once(ROOT_PDIR . 'core/libs/core/CoreDateTime.php');