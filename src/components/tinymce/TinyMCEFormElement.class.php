<?php
/**
 * Provides the form element integration with TinyMCE
 *
 * @package TinyMCE
 * @since 0.1
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Library or "Lesser" General Public License version 2.1
 */

class TinyMCEFormElement extends \Core\Forms\FormElement {
	public function __construct($atts = null) {
		// Some defaults
		$this->_attributes['class'] = 'formelement formwysiwyginput tinymce';
		$this->_attributes['rows']  = '25';
		
		parent::__construct($atts);
		
		// Note, I'm taking the required flag out from here; tinymce doesn't support it.
		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'readonly', 'tabindex', 'rows', 'cols', 'style', 'class');
	}
}

