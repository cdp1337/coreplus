<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130818.2333
 * @package PackageName
 * 
 * Created with JetBrains PhpStorm.
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
	// key is in the format of:
	// /user/displayname/displayoptions

	$key = $config->get('key');

	$gname = substr($key, 1);
	$gname = ucwords(substr($gname, 0, strpos($gname, '/')));


	$title = substr($key, strlen($gname) + 2);
	// Split the title on '/' and capitalize it to make it more user-friendly.
	$title = str_replace('/', ' ', $title);
	$title = ucwords($title);

	$val   = \ConfigHandler::Get($key);
	$name  = 'config[' . $key . ']';

	switch ($config->get('type')) {
		case 'string':
			$el = \FormElement::Factory('text');
			break;
		case 'enum':
			$el = \FormElement::Factory('select');
			$el->set('options', array_map('trim', explode('|', $config->get('options'))));
			break;
		case 'boolean':
			$el = \FormElement::Factory('radio');
			$el->set(
				'options', array('false' => 'No/False',
				                 'true'  => 'Yes/True')
			);
			if ($val == '1' || $val == 'true' || $val == 'yes') $val = 'true';
			else $val = 'false';
			break;
		case 'int':
			$el                    = \FormElement::Factory('text');
			$el->validation        = '/^[0-9]*$/';
			$el->validationmessage = $gname . ' - ' . $title . ' expects only whole numbers with no punctuation.';
			break;
		case 'set':
			$el = \FormElement::Factory('checkboxes');
			$el->set('options', array_map('trim', explode('|', $config->get('options'))));
			if(is_array($val)){
				// Yay, it's already setup
			}
			else{
				$val  = array_map('trim', explode('|', $val));
			}
			$name = 'config[' . $key . '][]';
			break;
		default:
			throw new \Exception('Unsupported configuration type for ' . $key . ', [' . $config->get('type') . ']');
			break;
	}

	$el->set('group', $gname);
	$el->set('title', $title);
	$el->set('name', $name);
	$el->set('value', $val);

	$desc = $config->get('description');
	if ($config->get('default_value') && $desc) $desc .= ' (default value is ' . $config->get('default_value') . ')';
	elseif ($config->get('default_value')) $desc = 'Default value is ' . $config->get('default_value');

	$el->set('description', $desc);

	return $el;
}