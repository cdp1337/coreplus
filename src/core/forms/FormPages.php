<?php
/**
 * Class file for the several FormPage* classes that exist.
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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

/**
 * Class FormPageRewriteURLInput provides a text input with custom javascript to pull from the title and urlify it.
 *
 * @package Core\Forms
 */
class FormPageRewriteURLInput extends FormTextInput{

}

/**
 * Input type for the theme selection input.  Only really useful from within the PageModel.
 *
 * @package Core\Forms
 */
class FormPageThemeSelectInput extends FormSelectInput{
	public function render(){

		// Give me all the skins available on the current theme.
		$skins = array('' => '-- Site Default Skin --');
		foreach(ThemeHandler::GetTheme(null)->getSkins() as $s){

			// Skip the "blank" template.  This is a special one that shouldn't be selectable.
			if($s['file'] == 'blank.tpl') continue;

			$n = ($s['title']) ? $s['title'] : $s['file'];
			if($s['default']) $n .= ' (default)';

			$skins[$s['file']] = $n;
		}
		if(sizeof($skins) <= 2){
			// This will just return a blank string, as it does not need to be rendered to the UA.
			// This means that there is only one skin on this theme, so displaying a select box with only
			// one option is pretty useless.
			return '';
		}

		// Set the options as the themes available.
		$this->set('options', $skins);

		// And continue!
		return parent::render();
	}
}

/**
 * Input type for the page selection input.  Only really useful from within the PageModel.
 *
 * @package Core\Forms
 */
class FormPagePageSelectInput extends FormSelectInput{
	public function __construct($atts = null){

		parent::__construct($atts);
	}

	public function render(){

		if(!$this->get('templatename')){
			throw new Exception('Unable to render pageselectinput element without templatename set!');
		}
		// Figure out the template directory for custom pages, (if it exists)
		// In order to get the types, I need to sift through all the potential template directories and look for a directory
		// with the matching name.
		$tmpname = substr($this->get('templatename'), 0, -4) . '/';

		$matches = array();

		foreach(\Core\Templates\Template::GetPaths() as $d){
			if(is_dir($d . $tmpname)){
				// Yay, sift through that and get the files!
				$dir = \Core\Filestore\Factory::Directory($d . $tmpname);
				foreach($dir->ls('tpl') as $file){
					// Skip directories
					if($file instanceof \Core\Filestore\Directory) continue;

					/** @var $file \Core\Filestore\File */
					//$fullpath = $tmpname . $file->getBaseFilename();
					$fullpath = $file->getBaseFilename();
					$name = $file->getBaseFilename();
					// Do some template updates and make it a little more friendlier to read.
					$name = ucwords(str_replace('-', ' ', substr($name, 0, -4))) . ' Template';
					$matches[ $fullpath ] = $name;
				}
			}
		}

		// Are there any matches?  If not just return a blank string.
		if(!sizeof($matches)){
			return '';
		}

		$options = array_merge(array('' => '-- Default Page Template --'), $matches);
		$this->set('options', $options);

		return parent::render();
	}
}

/**
 * Class FormPageParentSelectInput provides a select input with the list of selectable parent URLs in the system.
 *
 * @package Core\Forms
 */
class FormPageParentSelectInput extends FormSelectInput{

	public function setValue($value){
		// This strtoloweris required here because base baseurls are case insensitive, but the form system is case sensitive.
		return parent::setValue(strtolower($value));
	}

	public function render(){
		$f = new ModelFactory('PageModel');
		if ($this->get('baseurl')) $f->where('baseurl != ' . $this->get('baseurl'));
		$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');

		$this->set('options', $opts);

		// And continue!
		return parent::render();
	}
}

/**
 * Class FormPageInsertables
 *
 * @deprecated 2013.07.11 cpowell
 *             This has been migrated to the PageModel system.  Please use that if available.
 *
 * @package Core\Forms
 */
class FormPageInsertables extends FormGroup {

	/**
	 * A pointer to the page selector... remember it here just to save on lookups later.
	 *
	 * @var FormPagePageSelectInput
	 */
	private $_selector;

	public function  __construct($atts = null) {
		error_log(__CLASS__ . ' is candidate for removal, please change this code!', E_USER_DEPRECATED);

		// The inbound options may vary slightly.
		if(isset($atts['model']) && $atts['model'] instanceof PageModel){
			$page = $atts['model'];
			$atts['baseurl'] = $page->get('baseurl');
		}
		elseif(isset($atts['baseurl'])){
			$page = new PageModel($atts['baseurl']);
		}
		else{
			throw new Exception('pageinsertables form needs at least the parameter "model" or "baseurl"!');
		}


		// Some defaults
		if(!isset($atts['title'])) $atts['title'] = 'Page Content';
		if(!isset($atts['name'])) $atts['name'] = 'insertables';

		parent::__construct($atts);

		// The prefix for all elements on this group.
		$prefix = $this->get('name') ? $this->get('name') : 'insertables';

		// I need to add the selector here as well.  This select box should be with the insertables because
		// it directly affects the content options.
		$this->_selector = FormElement::Factory(
			'pagepageselect',
			array(
				'name' => $prefix . '_page_template',
				'title' => 'Alternative Page Template',
				'templatename' => $page->getBaseTemplateName(),
			)
		);

		// Remember, objects are passed by reference :)
		$this->addElement($this->_selector);

		$this->setTemplateName($page->get('page_template'));
	}

	public function setTemplateName($templatename){

		$prefix = $this->get('name');
		if($templatename === null) $templatename = ''; // Reset back to default.

		// First of all, check and see if this is already set.
		// This will work initially because the initial value is null, and the incoming string will be "".
		if($this->_selector->get('value') === $templatename) return;

		$this->_selector->set('value', $templatename);

		// Ok, it's not.... guess I should remove any existing elements then.
		foreach($this->_elements as $k => $el){
			if($el == $this->_selector) continue; // I suppose I shouldn't remove the selector itself...

			unset($this->_elements[$k]);
		}


		if($templatename){
			// Don't forget to prepend the base template!
			$templatename = substr($this->_selector->get('templatename'), 0, -4) . '/' . $templatename;
		}
		else{
			// Default!  I still need to transpose "" to the default template afterall.
			$templatename = $this->_selector->get('templatename');
		}



		// Translate the filename to an absolute path.
		$tpl = Core\Templates\Template::ResolveFile($templatename);
		if (!$tpl) return null;

		// Scan through $tpl and find any {insertable} tag.
		$tplcontents = file_get_contents($tpl);
		preg_match_all('/\{insertable(.*)\}(.*)\{\/insertable\}/isU', $tplcontents, $matches);

		// Guess this page had no insertables.
		if (!sizeof($matches[0])){
			return;
		}
		foreach ($matches[0] as $k => $v) {
			// The contents of the {insertable ...} tag.
			$tag     = trim($matches[1][$k]);
			// The contents inside of the tags.
			$content = trim($matches[2][$k]);
			$default = $content;

			// To make this tag searchable easily, convert it to an xml element and get the attributes from that.
			$simple = new SimpleXMLElement('<insertable ' . $tag . '/>');
			$attributes = array();
			foreach($simple->attributes() as $k => $v){
				$attributes[$k] = (string)$v;
			}

			$name = $attributes['name'];
			$title = isset($attributes['title']) ? $attributes['title'] : $name;
			$type = isset($attributes['type']) ? $attributes['type'] : null; // null means an automatic type.
			if(isset($attributes['default'])) $default = $attributes['default'];


			// This insertable may already have content from the database... if so I want to pull that!
			$i = new InsertableModel($this->get('baseurl'), $name);
			if ($i->get('value') !== null) $content = $i->get('value');

			// These will be the default options for creating form elements, extend them as necessary.
			$elementoptions = array(
				'name'  => $prefix . "[$name]",
				'title' => $title,
				'value' => $content,
			);
			if(isset($attributes['description'])) $elementoptions['description'] = $attributes['description'];

			// If the type is null, try to determine the form type based on the content.
			if($type === null){
				if (strpos($default, "\n") === false && strpos($default, "<") === false) {
					$type = 'text';
				}
				elseif (preg_match('/<img(.*?)>/i', $default)) {
					$type = 'image';
				}
				else {
					$type = 'wysiwyg';
				}
			}



			// Some elements have specific options that need to be set.
			switch($type){
				case 'image':
					$type = 'file';
					$elementoptions['accept'] = 'image/*';
					$elementoptions['basedir'] = 'public/insertable';
					break;
				case 'file':
					$elementoptions['basedir'] = 'public/insertable';
					break;
				case 'select':
					$elementoptions['options'] = array_map('trim', explode('|', $attributes['options']));
					break;
			}

			// Add the actual elements now!
			$this->addElement($type, $elementoptions);
		}
	}

	/*
	public function render(){
		// This is here to ensure that cached versions of this form will render the correct elements.
		if(!$this->_selector->get('value') && sizeof($this->_elements) > 1){
			foreach($this->_elements as $k => $el){
				if($el == $this->_selector) continue; // I suppose I shouldn't remove the selector itself...

				unset($this->_elements[$k]);
			}
		}

		// Ok, carry on!
		return parent::render();
	}
	*/

	/**
	 * Save the elements back to the database for the bound base_url.
	 */
	public function save() {
		// This is similar to the getModel method of the Form, but is done across multiple records instead of just one.
		$baseurl = $this->get('baseurl');
		$els     = $this->getElements(true, false);
		$prefix  = $this->get('name') ? $this->get('name') : 'insertables';

		foreach ($els as $e) {
			if (!preg_match('/^' . $prefix . '\[(.*?)\].*/', $e->get('name'), $matches)) continue;

			$i = new InsertableModel($baseurl, $matches[1]);
			$i->set('value', $e->get('value'));
			$i->save();
		}
	}

	public function getTemplateName() {
		return 'forms/groups/pageinsertables.tpl';
	}


} // class FormPageInsertables


/**
 * Provides inputs for editing:
 * rewriteurl, parenturl, theme template and page template
 * along with page insertables.
 *
 * @deprecated 2013.07.11 cpowell - Candidate for immediate removal.
 */
class FormPageMeta extends FormGroup {

	public function  __construct($atts = null) {
		error_log(__CLASS__ . ' is candidate for immediate removal, please change this code!', E_USER_DEPRECATED);

		// Defaults
		$this->_attributes['name']    = 'page';

		if ($atts instanceof PageModel) {
			parent::__construct(array('name' => 'page'));

			$page = $atts;
		}
		else {
			if(isset($atts['model']) && $atts['model'] instanceof PageModel){
				// Everything is based off the page.
				$page = $atts['model'];
				unset($atts['model']);

				parent::__construct($atts);
			}
			else{
				parent::__construct($atts);

				// BaseURL needs to be set for this to work.
				//if(!$this->get('baseurl')) return null;

				// Everything is based off the page.
				$page = new PageModel($this->get('baseurl'));
			}
		}

		$this->_attributes['baseurl'] = $page->get('baseurl');
		$name = $this->_attributes['name'];

		// I need to get a list of pages to offer as a dropdown for selecting the "parent" page.
		$f = new ModelFactory('PageModel');
		if ($this->get('baseurl')) $f->where('baseurl != ' . $this->get('baseurl'));
		$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');

		$this->addElement(
			'pageparentselect',
			array(
				'name'    => $name . "[parenturl]",
				'title'   => 'Parent Page',
				'value'   => strtolower($page->get('parenturl')),
				'options' => $opts
			)
		);

		// Title
		$this->addElement(
			'text', array(
				      'name'        => $name . "[title]",
				      'title'       => 'Title',
				      'value'       => $page->get('title'),
				      'description' => 'Every page needs a title to accompany it, this should be short but meaningful.',
				      'required'    => true
			      )
		);

		// Rewrite url.
		$this->addElement(
			'pagerewriteurl', array(
				                'name'        => $name . "[rewriteurl]",
				                'title'       => 'Page URL',
				                'value'       => $page->get('rewriteurl'),
				                'description' => 'Starts with a "/", omit ' . ROOT_URL,
				                'required'    => true
			                )
		);

		$this->addElement(
			'access', array(
				        'name'  => $name . "[access]",
				        'title' => 'Access Permissions',
				        'value' => $page->get('access')
			        )
		);

		$this->addElement(
			'pagemetas',
			array(
				'name' => $name . '_meta',
				'model' => $page,
			)
		);

		// Give me all the skins available on the current theme.
		$skins = array('' => '-- Site Default Skin --');
		foreach(ThemeHandler::GetTheme(null)->getSkins() as $s){
			$n = ($s['title']) ? $s['title'] : $s['file'];
			if($s['default']) $n .= ' (default)';
			$skins[$s['file']] = $n;
		}
		if(sizeof($skins) > 2){
			$this->addElement(
				'select', array(
					        'name'    => $name . "[theme_template]",
					        'title'   => 'Theme Skin',
					        'value'   => $page->get('theme_template'),
					        'options' => $skins
				        )
			);
		}
	}

	/**
	 * Save the elements back to the database for the bound base_url.
	 */
	public function save() {

		$page = $this->getModel();

		//if($this->get('cachedaccess')) $page->set('access', $this->get('cachedaccess'));
		//if($this->get('cachedtitle')) $page->set('title', $this->get('cachedtitle'));
		return $page->save();

		// Ensure the children have the right baseurl if that changed.
		$els = $this->getElements();
		foreach ($els as $e) {
			if (!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;
			$e->set('baseurl', $this->get('baseurl'));
		}

		// And all the insertables.
		$i = $this->getElementByName('insertables');
		$i->save();

		return true;
	}

	/**
	 * Get the model for the page subform.
	 * This is on the group because a page object can be set embedded in another form.
	 *
	 * @param null $page
	 *
	 * @return null|PageModel
	 */
	public function getModel($page = null) {
		// Allow linked models.
		if (!$page) $page = new PageModel($this->get('baseurl'));

		// Because name can be changed.
		$name = $this->_attributes['name'];

		$els = $this->getElements(true, false);
		foreach ($els as $e) {
			/** @var $e FormElement */

			if (!preg_match('/^[a-z_]*\[(.*?)\].*/', $e->get('name'), $matches)) continue;

			$key = $matches[1];
			$val = $e->get('value');

			// Meta attributes
			if(strpos($e->get('name'), $name . '_meta') === 0){
				$val = $e->get('value');
				$page->setMeta($key, $val);
			}
			elseif(strpos($e->get('name'), $name) === 0){
				$page->set($key, $val);
			}
			else{
				continue;
			}
		}

		return $page;

		// Add in any insertables too, if they're attached.
		// DISABLING 2012.05 cpowell
		/*
		if(($i = $this->getElementByName('insertables'))){
			$els = $i->getElements();
			foreach($els as $e){
				if(!preg_match('#^insertable\[(.*?)\].*#', $e->get('name'), $matches)) continue;

				$submodel = $page->findLink('Insertable', array('name' => $matches[1]));
				$submodel->set('value', $e->get('value'));
			}
		}

		return $page;
		*/
	}

	/**
	 * This group does not have a template of its own, should be rendered directly to the form.
	 * @return null
	 */
	public function getTemplateName() {
		return null;
	}

//

} // class FormPageInsertables