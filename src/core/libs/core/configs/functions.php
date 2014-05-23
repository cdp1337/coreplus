<?php
/**
 * Common functions used in conjunction with the Config system.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130818.2333
 * @package Core
 */

namespace Core\Configs;

/**
 * Transpose a populated form element from the underlying ConfigModel object.
 * Will populate the name, options, validation, etc.
 *
 * @param \ConfigModel $config
 * @return \FormElement
 *
 * @throws \Exception
 */
function get_form_element_from_config(\ConfigModel $config){
	return $config->asFormElement();
}
