<?php
/**
 * All core Form objects in the system
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

class FormGroup{
	protected $_elements;

	protected $_attributes;

	protected $_validattributes = array();
	
	/**
	 * Boolean if this form element requires a file upload.
	 * Only "file" type elements should require this.
	 * 
	 * @var boolean
	 */
	public $requiresupload = false;

	public function __construct($atts = null){
		$this->_attributes = array();
		$this->_elements = array();

		if($atts) $this->setFromArray ($atts);
	}
	
	public function set($key, $value){
		$this->_attributes[strtolower($key)] = $value;
	}
	
	public function get($key){
		$key = strtolower($key);
		return (isset($this->_attributes[$key]))? $this->_attributes[$key] : null;
	}

	public function setFromArray($array){
		foreach($array as $k => $v){
			$this->set($k, $v);
		}
	}

	public function hasError(){
		foreach($this->_elements as $e){
			if($e->hasError()) return true;
		}

		return false;
	}

	public function getErrors(){
		$err = array();
		foreach($this->_elements as $e){
			if($e instanceof FormGroup) $err = array_merge($err, $e->getErrors());
			elseif($e->hasError()) $err[] = $e->getError();
		}
		return $err;
	}

	public function addElement($element, $atts = null){
		// Since this allows for just plain names to be submitted, translate
		// them to the form object to be rendered.
		
		if($element instanceof FormElement || is_a($element, 'FormElement')){
			// w00t, already in the right format!
			if($atts) $element->setFromArray ($atts);
			$this->_elements[] = $element;
		}
		elseif($element instanceof FormGroup){
			// w00t, already in the right format!
			if($atts) $element->setFromArray ($atts);
			$this->_elements[] = $element;
		}
		else{
			if(!isset(Form::$Mappings[$element])) $element = 'text'; // Default.

			$this->_elements[] = new Form::$Mappings[$element]($atts);
		}
	}
	
	public function switchElement(FormElement $oldelement, FormElement $newelement){
		foreach($this->_elements as $k => $el){
			// A match found?  Replace it!
			if($el == $oldelement){
				$this->_elements[$k] = $newelement;
				return true;
			}
			
			// If the element was another group, tell that group to scan too!
			if($el instanceof FormGroup){
				// Scan this object too!
				if($el->switchElement($oldelement, $newelement)) return true;
			}
		}
		
		// No replacement?...
		return false;
	}

	public function getTemplateName(){
		return 'forms/groups/default.tpl';
	}

	public function render(){
		$out = '';
		foreach($this->_elements as $e){
			$out .= $e->render();
		}

		$file = $this->getTemplateName();

		// Groups may not have a template... if so just render the children directly.
		if(!$file) return $out;

		$tpl = new Template();
		$tpl->assign('group', $this);
		$tpl->assign('elements', $out);
		return $tpl->fetch($file);
	}
	
	/**
	 * Template helper function
	 * gets the css class of the element.
	 * @return string
	 */
	public function getClass(){
		$c = $this->get('class');
		$r = $this->get('required');
		$e = $this->hasError();

		return $c . (($r)? ' formrequired' : '') . (($e)? ' formerror' : '');
	}

	/**
	 * Template helper function
	 * gets the input attributes as a string
	 * @return string
	 */
	public function getGroupAttributes(){
		$out = '';
		foreach($this->_validattributes as $k){
			if(($v = $this->get($k))) $out .= " $k=\"" . str_replace('"', '\\"', $v) . "\"";
		}
		return $out;
	}

	/**
	 * Get all elements in this group.
	 * 
	 * @param boolean $recursively Recurse into subgroups.
	 * @param boolean $includegroups Include those subgroups (if recursive is enabled)
	 * @return array
	 */
	public function getElements($recursively = true, $includegroups = false){
		$els = array();
		foreach($this->_elements as $e){
			// Tack on this element, regardless of what it is.
			//$els[] = $e;
			
			// Only include a group if recusively is set to false or includegroups is set to true.
			if(
				$e instanceof FormElement ||
				($e instanceof FormGroup && ( $includegroups || !$recursively ) )
			){
				$els[] = $e;
			}

			// In addition, if it is a group, delve into its children.
			if($recursively && $e instanceof FormGroup) $els = array_merge($els, $e->getElements ($recursively));
		}
		return $els;
	}
	
	/**
	 * Lookup and return an element based on its name.
	 * 
	 * Shortcut of getElementByName()
	 * 
	 * @param string $name The name of the element to lookup.
	 * @return FormElement
	 */
	public function getElement($name){
		return $this->getElementByName($name);
	}

	/**
	 * Lookup and return an element based on its name.
	 * 
	 * @param string $name The name of the element to lookup.
	 * @return FormElement
	 */
	public function getElementByName($name){
		$els = $this->getElements(true, true);

		foreach($els as $el){
			if($el->get('name') == $name) return $el;
		}
		
		return false;
	}
}

class FormElement{
	/**
	 * Array of attributes for this form element object.
	 * Should be in key/value pair.
	 * 
	 * @var array
	 */
	protected $_attributes = array();

	protected $_error;

	/**
	 * Array of attributes to automatically return when getInputAttributes() is called.
	 * 
	 * @var array
	 */
	protected $_validattributes = array();
	
	/**
	 * Boolean if this form element requires a file upload.
	 * Only "file" type elements should require this.
	 * 
	 * @var boolean
	 */
	public $requiresupload = false;
	
	/**
	 * An optional validation check for this element.
	 * This can be multiple things, such as:
	 * 
	 * "/blah/" - Evaluated with preg_match.
	 * "#blah#" - Also evaluated with preg_match.
	 * "MyFoo::Blah" - Evaluated with call_user_func.
	 * 
	 * @var string 
	 */
	public $validation = null;
	
	/**
	 * An optional message to post if the validation check fails.
	 * 
	 * @var string 
	 */
	public $validationmessage = null;

	public function __construct($atts = null){

		if($atts) $this->setFromArray ($atts);
	}

	public function set($key, $value){
		$key = strtolower($key);

		switch($key){
			case 'value': // Drop into special logic.
				$this->setValue($value);
				break;
			case 'label': // This is an alias for title.
				$this->_attributes['title'] = $value;
			case 'options':
				// This will require a little bit more attention, as if only the title
				// is given, use that for the value as well.
				if(!is_array($value)){
					$this->_attributes[$key] = $value;
				}
				else{
					$o = array();
					foreach($value as $k => $v){
						if(is_numeric($k)) $o[$v] = $v;
						else $o[$k] = $v;
					}
					$this->_attributes[$key] = $o;
				}
				break;
			default:
				$this->_attributes[$key] = $value;
				break;
		}
	}

	public function get($key){
		$key = strtolower($key);

		switch($key){
			case 'label': // Special case, returns either title or name, whichever is set.
				if(!empty($this->_attributes['title'])) return $this->_attributes['title'];
				else return $this->get('name');
				break;
			case 'id': // ID is also a special case, it casn use the name if not defined otherwise.
				if(!empty($this->_attributes['id'])) return $this->_attributes['id'];
				else return $this->get('name');
				break;
			default:
				return (isset($this->_attributes[$key]))? $this->_attributes[$key] : null;
		}
	}
	
	/**
	 * Get all attributes of this form element as a flat array.
	 * @return array
	 */
	public function getAsArray(){
		$ret = array();
		$ret['__class'] = get_class($this);
		foreach($this->_attributes as $k => $v){
			$ret[$k] = (isset($this->_attributes[$k]))? $this->_attributes[$k] : null;
		}
		return $ret;
	}

	public function setFromArray($array){
		foreach($array as $k => $v){
			$this->set($k, $v);
		}
	}

	/**
	 * This set explicitly handles the value, and has the extended logic required
	 *  for error checking and validation.
	 *
	 * @param mixed $value
	 */
	public function setValue($value){
		if($this->get('required') && !$value){
			$this->_error = $this->get('label') . ' is required.';
			return false;
		}

		// If there's a value, pass it through the validation check, (if available).
		if($value && $this->validation){
			$vmesg = $this->validationmessage ? $this->validationmessage : $this->get('label') . ' does not validate correctly, please double check it.';
			$v = $this->validation;
			
			// @todo Add support for a variety of validation logics maybe???
			
			// Method-based validation.
			if( strpos($v, '::') !== false && ($out = call_user_func($v, $value)) !== true ){
				// If a string was returned from the validation logic, set the error to that string.
				if($out !== false) $vmesg = $out;
				$this->_error = $vmesg;
				return false;
			}
			// regex-based validation.  These don't have any return strings so they're easier.
			elseif(
				($v{0} == '/' && !preg_match($v, $value)) ||
				($v{0} == '#' && !preg_match($v, $value))
			){
				if(DEVELOPMENT_MODE) $vmesg .= ' validation used: ' . $v;
				$this->_error = $vmesg;
				return false;
			}
		}

		$this->_attributes['value'] = $value;
		return true;
	}

	public function hasError(){
		return ($this->_error);
	}

	public function getError(){
		return $this->_error;
	}

	public function setError($err, $displayMessage = true){
		$this->_error = $err;
		if($err && $displayMessage) Core::SetMessage ($err, 'error');
	}
	
	public function clearError(){
		$this->setError(false);
	}

	public function getTemplateName(){
		return 'forms/elements/' . strtolower(get_class($this)) . '.tpl';
	}

	public function render(){
		
		// If multiple is set, but the name does not have a [] at the end.... add it.
		if($this->get('multiple') && ! preg_match('/.*\[.*\]/', $this->get('name'))) $this->_attributes['name'] .= '[]';

		$file = $this->getTemplateName();

		$tpl = new Template();

		$tpl->assign('element', $this);

		return $tpl->fetch($file);
	}

	/**
	 * Template helper function
	 * gets the css class of the element.
	 * @return string
	 */
	public function getClass(){
		$c = $this->get('class');
		$r = $this->get('required');
		$e = $this->hasError();

		return $c . (($r)? ' formrequired' : '') . (($e)? ' formerror' : '');
	}

	/**
	 * Template helper function
	 * gets the input attributes as a string
	 * @return string
	 */
	public function getInputAttributes(){
		$out = '';
		foreach($this->_validattributes as $k){
			// 'Required' is skipped if it's false.
			if($k == 'required' && !$this->get($k)) continue;
			
			if(($v = $this->get($k)) !== null) $out .= " $k=\"" . str_replace('"', '\\"', $v) . "\"";
		}
		return $out;
	}

	/**
	 * Lookup the value from $src array for this given element.
	 * Handles all name/array resolution automatically.
	 *
	 * Note, this does NOT set the value, only looks up the value from the array.
	 *
	 * @param array $src
	 * @return mixed
	 */
	public function lookupValueFrom(&$src){
		$n = $this->get('name');
		if(strpos($n, '[') !== false){
			$base = substr($n, 0, strpos($n, '['));
			if(!isset($src[$base])) return null;
			$t = $src[$base];
			preg_match_all('/\[(.+?)\]/', $n, $m);
			foreach($m[1] as $k){
				if(!isset($t[$k])) return null;
				$t = $t[$k];
			}
			// Now $t should be the value of the POSTed value!
			return $t;
		}
		else{
			if(!isset($src[$n])) return null;
			else return $src[$n];
		}
	}
	
	/**
	 * Get the appropriate form element based on the incoming type.
	 * 
	 * @param string $type
	 * @param array $attributes
	 * @return FormElement 
	 */
	public static function Factory($type, $attributes = array()){
		if(!isset(Form::$Mappings[$type])) $type = 'text'; // Default.

		return new Form::$Mappings[$type]($attributes);
	}
}

/**
 * The main Form object.
 */
class Form extends FormGroup{

	/**
	 * Standard mappings for 'text' to class of the FormElement.
	 * This can be extended, ie: wysiwyg or captcha.
	 * 
	 * @var array
	 */
	public static $Mappings = array(
		'checkbox' => 'FormCheckboxInput',
		'checkboxes' => 'FormCheckboxesInput',
		'file' => 'FormFileInput',
		'hidden' => 'FormHiddenInput',
		'pageinsertables' => 'FormPageInsertables',
		'pagemeta' => 'FormPageMeta',
		'password' => 'FormPasswordInput',
		'radio' => 'FormRadioInput',
		'select' => 'FormSelectInput',
		'submit' => 'FormSubmitInput',
		'text' => 'FormTextInput',
		'textarea' => 'FormTextareaInput',
		'time' => 'FormTimeInput',
		'wysiwyg' => 'FormTextareaInput',
	);

	/**
	 * Construct a new Form object
	 * @param array $atts Array of attribute to assign to this form off the bat.
	 */
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_validattributes = array('accept', 'accept-charset', 'action', 'enctype', 'id', 'method', 'name', 'target', 'style');
		//$this->_attributes['uniqueid'] = rand(1, 4) . Core::RandomHex(7);
		$this->_attributes['method'] = 'POST';
	}

	public function getTemplateName(){
		return 'forms/form.tpl';
	}

	/**
	 * Render this form and all inside elements to valid HTML.
	 * 
	 * This will also save the form to the session data for post-submission validation.
	 *  (if called with null or "foot")
	 * 
	 * @param mixed $part "body|head|foot| or null
	 *        Render just a specific part of the form.  Useful for advanced usage.
	 *        null: Render all of the form and its element.
	 *        "head": Render just the beginning of the form, including the <form> opening tag.
	 *        "body": Render just the body of the form, specifically the elements.
	 *        "foot": Render just the end of the form, including the </form> closing tag.
	 * @return string (valid HTML)
	 */
	public function  render($part = null) {
		
		// Check and see if there are any elements in this form that require a fileupload.
		foreach($this->getElements() as $e){
			if($e->requiresupload){
				$this->set('enctype', 'multipart/form-data');
				break;
			}
		}
		
		// Will be used to know if the errors in elements should be removed prior to rendering.
		$ignoreerrors = false;
		
		// Slip in the formid tracker to remember this submission.
		if(($part === null || $part == 'body') && $this->get('callsmethod')){
			$e = new FormHiddenInput(array('name' => '___formid', 'value' => $this->get('uniqueid')));
			$this->_elements = array_merge(array($e), $this->_elements);

			// I need to ensure a repeatable but unique id for this form.
			// Essentially when this form is submitted, I need to be able to know that it's the same form upon re-rendering.
			if(!$this->get('uniqueid')){
				$hash = '';
				
				if($this->get('___modelpks')){
					foreach($this->get('___modelpks') as $k => $v){
						$hash .= $k . ':' . $v . ';';
					}
				}

				foreach($this->getElements() as $el){
					$hash .= get_class($el) . ':' . $el->get('name') . ';';
				}
				// Hash it!
				$hash = md5($hash);
				$this->set('uniqueid', $hash);
				$this->getElementByName('___formid')->set('value', $hash);
			}
			
			// Was this form already submitted, (and thus saved in the session?
			// If so, render that form instead!  This way the values get transported seemlessly.
			if(isset($_SESSION['FormData'][$this->get('uniqueid')])){
				if(($savedform = unserialize($_SESSION['FormData'][$this->get('uniqueid')]))){
					$this->_elements = $savedform->_elements;
				}
				else{
					$ignoreerrors = true;
				}
			}
			else{
				$ignoreerrors = true;
			}
		}
		
		if($ignoreerrors){
			foreach($this->getElements(true) as $el){
				$el->setError(false);
			}
		}
		
		$tpl = new Template();
		$tpl->assign('group', $this);
		if($part === null || $part == 'body'){
			$els = '';
			// Fill in the elements
			foreach($this->_elements as $e){
				$els .= $e->render();
			}
			$tpl->assign('elements', $els);
		}
		
		switch($part){
			case null:   $out = $tpl->fetch('forms/form.tpl');      break;
			case 'head': $out = $tpl->fetch('forms/form.head.tpl'); break;
			case 'body': $out = $tpl->fetch('forms/form.body.tpl'); break;
			case 'foot': $out = $tpl->fetch('forms/form.foot.tpl'); break;
		}
		
		// Save it
		if(($part === null || $part == 'foot') && $this->get('callsmethod')){
			$this->saveToSession();
		}

		return $out;
	}

	/**
	 * Get the associated model for this form, if there is one.
	 * This model will also be populated automatically with all the data submitted.
	 *
	 * @return Model
	 */
	public function getModel(){
		$m = $this->get('___modelname');
		if(!$m) return null; // A model needs to be defined first of all...

		$model = new $m();
		
		if(!$model instanceof Model) return null; // It needs to be a model... :/

		// Set the PK's...
		if(is_array($this->get('___modelpks'))){
			foreach($this->get('___modelpks') as $k => $v){
				$model->set($k, $v);
			}

			// It should now be loadable.
			$model->load();
		}

		// Now, get every "model[...]" element, as they key up 1-to-1.
		$els = $this->getElements(true, false);
		foreach($els as $e){
			if(!preg_match('/^model\[(.*?)\].*/', $e->get('name'), $matches)) continue;

			$key = $matches[1];
			$val = $e->get('value');
			$schema = $model->getKeySchema($key);
			
			if($schema['type'] == Model::ATT_TYPE_BOOL){
				// This is used by checkboxes
				if(strtolower($val) == 'yes') $val = 1;
				// A single checkbox will have the value of "on" if checked
				elseif(strtolower($val) == 'on') $val = 1;
				// Hidden inputs will have the value of "1"
				elseif($val == 1) $val = 1;
				else $val = 0;
			}
			
			$model->set($key, $val);
		}
		
		
		return $model;
		
		// The below logic in this method is no longer functional yet.
		
		
		// Add support for inline Pages for models.
		if($model->get('baseurl') && $model->getLink('Page') instanceof PageModel && $this->getElementByName('page')){
			$page = $model->getLink('Page');
			
			// Update the cached information in the page.
			if($model->get('title') !== null) $page->set('title', $model->get('title'));
			if($model->get('access') !== null) $page->set('access', $model->get('access'));
			
			// Tack on the Page data too!
			$this->getElementByName('page')->getModel($page);
		}
		
		
		// Add support for inline Widgets for models.
		if($model->get('baseurl') && $model->getLink('Widget') instanceof WidgetModel){
			// All I have to do is just "get" it.... that's it!
			// The save algorithm will do the rest.
			$widget = $model->getLink('Widget');
			
			// Update the cached information in the page.
			if($model->get('title') !== null) $widget->set('title', $model->get('title'));
			if($model->get('access') !== null) $widget->set('access', $model->get('access'));
			
			// Tack on the Page data too!
			//$this->getElementByName('page')->getModel($page);
		}
		

		return $model;
	}
	
	/**
	 * Load this form's values from the provided array, usually GET or POST.
	 * This is really an internal function that should not be called externally.
	 * 
	 * @param array $src 
	 */
	public function loadFrom($src){
		$els = $this->getElements(true, false);
		foreach($els as $e){
			// Be sure to clear any errors from the previous page load....
			$e->clearError();
			$e->set('value', $e->lookupValueFrom($src));
			if($e->hasError()) Core::SetMessage($e->getError(), 'error');
		}
	}
	
	/**
	 * Switch an element type from one to another.
	 * This is useful for doing some fine tuning on a pre-generated form, ie
	 *  a "string" field in the Model should be interperuted as an image upload.
	 * 
	 * @param string $elementname The name of the element to switch
	 * @param string $newtype The standard name of the new element type
	 * @return boolean Return true on success, false on failure.
	 */
	public function switchElementType($elementname, $newtype){
		$el = $this->getElement($elementname);
		if(!$el) return false;
		
		// Default.
		if(!isset(self::$Mappings[$newtype])) $newtype = 'text';
		
		$cls = self::$Mappings[$newtype];
		
		// If it's already the newtype, no change required.
		if(get_class($el) == $cls) return false;
		
		$atts = $el->getAsArray();
		
		// Don't need this one
		unset($atts['__class']);
		$newel = new $cls();
		$newel->setFromArray($atts);
		//var_dump($el, $atts, $newel, $newel->getInputAttributes());
		$this->switchElement($el, $newel);
		return true;
	}
	
	/**
	 * Internal method to save a serialized version of this object
	 *	 into the database so it can be loaded upon submitting.
	 *
	 * @return void
	 */
	private function saveToSession(){
		
		if(!$this->get('callsmethod')) return; // Don't save anything if there's no method to call.

		$this->set('expires', Time::GetCurrent() + 1800); // 30 minutes
		
		$_SESSION['FormData'][$this->get('uniqueid')] = serialize($this);
	}

	
	/**
	 * Function that is fired off on page load.
	 * This checks if a form was submitted and that form was present in the SESSION.
	 * 
	 * @return null
	 */
	public static function CheckSavedSessionData(){
		// There has to be data in the session.
		if(!(isset($_SESSION['FormData']) && is_array($_SESSION['FormData']))) return;

		$formid = (isset($_REQUEST['___formid']))? $_REQUEST['___formid'] : false;
		$form = false;

		foreach($_SESSION['FormData'] as $k => $v){
			// If the object isn't a valid object after unserializing...
			if(!($el = unserialize($v))){
				unset($_SESSION['FormData'][$k]);
				continue;
			}

			// Check the expires time
			if($el->get('expires') <= Time::GetCurrent()){
				unset($_SESSION['FormData'][$k]);
				continue;
			}

			if($k == $formid){
				// Remember this for after all the checks have finished.
				$form = $el;
			}
		}
		
		// No form found... simple enough
		if(!$form) return;

		// Ensure the submission types match up.
		if(strtoupper($form->get('method')) != $_SERVER['REQUEST_METHOD']){
			Core::SetMessage('Form submission type does not match', 'error');
			return;
		}

		// Run though each element submitted and try to validate it.
		if(strtoupper($form->get('method')) == 'POST') $src =& $_POST;
		else $src =& $_GET;

		$form->loadFrom($src);

		// Still good?
		if(!$form->hasError()) $status = call_user_func($form->get('callsmethod'), $form);
		else $status = false;

		// Regardless, bundle this form back into the session so the controller can use it if needed.
		$_SESSION['FormData'][$formid] = serialize($form);

		// Fail statuses.
		if($status === false) return;
		if($status === null) return;

		// Guess it's not false and not null... must be good then.

		// @todo Handle an internal save procedure for "special" groups such as pageinsertables and what not.

		// Cleanup
		unset($_SESSION['FormData'][$formid]);

		// If it's set to die, simply exit the script without outputting anything.
		if($status == 'die') exit;
		elseif($status === true) Core::Reload();
		else Core::Redirect($status);
	}

	/**
	 * Scan through a standard Model object and populate elements with the correct fields and information.
	 * 
	 * @param Model $model
	 * @return Form 
	 */
	public static function BuildFromModel(Model $model){
		$f = new Form();

		// Add the initial model tracker, will remember which model is attached.
		$f->set('___modelname', get_class($model));
		$s = $model->getKeySchemas();
		$i = $model->GetIndexes();
		if(!isset($i['primary'])) $i['primary'] = array();

		$new = $model->isnew();

		if(!$new){
			// Save the PKs of this model in the SESSION data so they don't have to be sent to the browser.
			$pks = array();
			foreach($i['primary'] as $k => $v){
				$pks[$v] = $model->get($v);
			}
			$f->set('___modelpks', $pks);
		}

		foreach($s as $k => $v){
			// Skip the AI column if it doesn't exist.
			if($new && $v['type'] == Model::ATT_TYPE_ID) continue;

			// These are already taken care above in the SESSION data.
			if(!$new && in_array($k, $i['primary'])) continue; 

			// Set the title from either the explicit formtitle or the key itself.
			if(isset($v['formtitle'])) $title = $v['formtitle'];
			else $title = ucwords($k);
			
			$required = (isset($v['required']))? ($v['required']) : false;
			
			if($model->get($k)) $val = $model->get($k);
			elseif(isset($v['default'])) $val = $v['default'];
			else $val = null;
			
			
			// Boolean checkboxes can have special options.
			//if(isset($v['formtype']) && $v['formtype'] == 'checkbox' && $v['type'] == Model::ATT_TYPE_BOOL){
			//	$el = FormElement::Factory($v['formtype']);
			//	$el->set('options', array('1'));
			//}
			// Standard form types.
			if(isset($v['formtype'])){
				$el = FormElement::Factory($v['formtype']);
			}
			elseif($v['type'] == Model::ATT_TYPE_BOOL){
				$el = FormElement::Factory('radio');
				$el->set('options', array('Yes', 'No'));
				
				if($model->get($k)) $val = 'Yes';
				elseif($model->get($k) === null && $v['default']) $val = 'Yes';
				elseif($model->get($k) === null && !$v['default']) $val = 'No';
				else $val = 'No';
			}
			elseif($v['type'] == Model::ATT_TYPE_STRING){
				$el = FormElement::Factory('text');
			}
			elseif($v['type'] == Model::ATT_TYPE_INT){
				$el = FormElement::Factory('text');
			}
			elseif($v['type'] == Model::ATT_TYPE_TEXT){
				$el = FormElement::Factory('textarea');
			}
			elseif($v['type'] == Model::ATT_TYPE_CREATED){
				// This element doesn't need to be in the form.
				continue;
			}
			elseif($v['type'] == Model::ATT_TYPE_UPDATED){
				// This element doesn't need to be in the form.
				continue;
			}
			elseif($v['type'] == Model::ATT_TYPE_ENUM){
				$el = FormElement::Factory('select');
				$opts = $v['options'];
				if($v['null']) $opts = array_merge(array('' => '-Select One-'), $opts);
				$el->set('options', $opts);
				if($v['default']) $el->set('value', $v['default']);
			}
			else{
				die('Unsupported model attribute type for Form Builder [' . $v['type'] . ']');
			}
			
			$el->set('name', 'model[' . $k . ']');
			$el->set('required', $required);
			$el->set('title', $title);
			$el->set('value', $val);
			
			$f->addElement($el);
		}
		/*
		// If this model supports Pages, add that too!
		if($model->get('baseurl') && $model->getLink('Page') instanceof PageModel){
			// Tack on the page meta inputs for this page.
			// This will include the rewriteurl, parenturl, theme template and page template.
			$f->addElement('pagemeta', $model->getLink('Page'));
		}
		*/
		return $f;
	}
}

class FormTextInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formtextinput';
		$this->_validattributes = array('accesskey', 'autocomplete', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'size', 'tabindex', 'width', 'height', 'value', 'style');
	}
}

class FormPasswordInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formpasswordinput';
		$this->_validattributes = array('accesskey', 'autocomplete', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'size', 'tabindex', 'width', 'height', 'value', 'style');
	}
}

class FormSubmitInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formsubmitinput';
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'size', 'tabindex', 'width', 'height', 'value', 'style');
	}
}

class FormTextareaInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formtextareainput';
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'rows', 'cols', 'style', 'class');
	}
}

class FormHiddenInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_validattributes = array('id', 'lang', 'name', 'value');
	}
}

class FormSelectInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formselect';
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'rows', 'cols');
	}
}


class FormRadioInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formradioinput';
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}
	
	/**
	 * Return the key of the currently checked value.
	 * This will intelligently scan for Yes/No values.
	 */
	public function getChecked() {
		// If this is a boolean (yes/no) radio option and a true or false
		// is set to the value, it should correctly propagate to "Yes" or "No"
		if(!isset($this->_attributes['value'])){
			return null;
		}
		elseif(
			isset($this->_attributes['options']) &&
			is_array($this->_attributes['options']) &&
			sizeof($this->_attributes['options']) == 2 &&
			isset($this->_attributes['options']['Yes']) &&
			isset($this->_attributes['options']['No'])
		){
			// Running strtolower on a boolean will result in either "1" or "".
			switch(strtolower($this->_attributes['value'])){
				case '1':
				case 'true':
				case 'yes':
					return 'Yes';
					break;
				default:
					return 'No';
					break;
			}
		}
		else{
			return $this->_attributes['value'];
		}
	}
	
}

class FormCheckboxesInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formcheckboxinput';
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}
	
	public function get($key) {
		if($key == 'value' && sizeof($this->_attributes['options']) > 1){
			// This should return an array if there are more than 1 option.
			if(!$this->_attributes['value']) return array();
			else return $this->_attributes['value'];
		}
		else{
			return parent::get($key);
		}
	}
	
	public function set($key, $value) {
		if($key == 'options'){
			// The options need to be an array, (hence the plural use)
			if(!is_array($value)) return false;
			
			// if every key in this is an int, transpose the value over to the key instead.
			// This allows for having an option with a different title and value.
			// (and cheating, not actually checking every key)
			if( isset($value[0]) && isset($value[sizeof($value) -1]) ){
				foreach($value as $k => $v){
					unset($value[$k]);
					$value[$v] = $v;
				}
			}
			
			return parent::set($key, $value);
		}
		else{
			return parent::set($key, $value);
		}
	}
	
}

class FormCheckboxInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formcheckboxinput';
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}
	
	/*public function get($key) {
		if($key == 'value' && sizeof($this->_attributes['options']) > 1){
			// This should return an array if there are more than 1 option.
			if(!$this->_attributes['value']) return array();
			else return $this->_attributes['value'];
		}
		else{
			return parent::get($key);
		}
	}*/
	
	/*public function set($key, $value) {
		if($key == 'value'){
			if($value) $this->_attributes['value'] = true;
			else $this->_attributes['value'] = false;
		}
		else{
			return parent::set($key, $value);
		}
	}*/
	
}



class FormPageInsertables extends FormGroup{

	public function  __construct($atts = null) {

		parent::__construct($atts);

		// Some defaults
		if(!$this->get('title')) $this->set('title', 'Page Content');
		
		// BaseURL needs to be set for this to work.
		if(!$this->get('baseurl')) return null;

		$p = new PageModel($this->get('baseurl'));

		// Ensure I can get the filename.
		$tpl = $p->getTemplateName();
		if(!$tpl) return null;
		$tpl = Template::ResolveFile($tpl);
		if(!$tpl) return null;

		// Scan through $tpl and find any {insertable} tag.
		$tplcontents = file_get_contents($tpl);
		preg_match_all('/\{insertable(.*)\}(.*)\{\/insertable\}/isU', $tplcontents, $matches);

		// Guess this page had no insertables.
		if(!sizeof($matches[0])) return null;
		foreach($matches[0] as $k => $v){
			$tag = trim($matches[1][$k]);
			$content = trim($matches[2][$k]);

			// Pull out the name and label of this insertable.
			$name = preg_replace('/.*name=["\'](.*?)["\'].*/i', '$1', $tag);
			$title = preg_replace('/.*title=["\'](.*?)["\'].*/i', '$1', $tag);
			
			// This insertable may already have content from the database... if so I want to pull that!
			$i = new InsertableModel($this->get('baseurl'), $name);
			if($i->get('value') !== null) $content = $i->get('value');

			// Determine what the content is intelligently.  (or at least try to...)
			if(strpos($content, "\n") === false && strpos($content, "<") === false){
				// Regular text insert.
				$this->addElement('text', array('name' => "insertable[$name]", 'title' => $title, 'value' => $content));
			}
			elseif(preg_match('/<img(.*?)src=["\'](.*?)["\'](.*?)>/i', $content)){
				// It's an image.
				// @todo Image Upload form element
				//$el = new FormIm
			}
			else{
				// Just default back to a WYSIWYG.
				$this->addElement('wysiwyg', array('name' => "insertable[$name]", 'title' => $title, 'value' => $content));
			}
		}
	}

	/**
	 * Save the elements back to the database for the bound base_url.
	 */
	public function save(){
		// This is similar to the getModel method of the Form, but is done across multiple records instead of just one.
		$baseurl = $this->get('baseurl');
		$els = $this->getElements(true, false);
		foreach($els as $e){
			if(!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;

			$i = new InsertableModel($baseurl, $matches[1]);
			$i->set('value', $e->get('value'));
			$i->save();
		}
	}

} // class FormPageInsertables


/**
 * Provides inputs for editing:
 * rewriteurl, parenturl, theme template and page template
 * along with page insertables.
 */
class FormPageMeta extends FormGroup{

	public function  __construct($atts = null) {
		
		if($atts instanceof PageModel){
			parent::__construct();
			
			$page = $atts;
			$this->_attributes['baseurl'] = $page->get('baseurl');
			$this->_attributes['name'] = 'page';
		}
		else{
			parent::__construct($atts);
			
			// BaseURL needs to be set for this to work.
			//if(!$this->get('baseurl')) return null;
			
			// Everything is based off the page.
			$page = new PageModel($this->get('baseurl'));
		}
		
		// Title
		$this->addElement('text', array(
			'name' => 'page[title]',
			'title' => 'Title',
			'value' => $page->get('title'),
			'description' => 'Every page needs a title to accompany it, this should be short but meaningful.',
			'required' => true
		));
		
		// Rewrite url.
		$this->addElement('text', array(
			'name' => 'page[rewriteurl]',
			'title' => 'Rewrite URL',
			'value' => $page->get('rewriteurl'),
			'description' => 'Starts with a "/", omit ' . ROOT_URL,
			'required' => true
		));
		
		
		// Author
		$this->addElement('text', array(
			'name' => 'page[metaauthor]',
			'title' => 'Author',
			'description' => 'Completely optional, but feel free to include it if relevant',
			'value' => $page->getMeta('author')
		));
		
		// Meta Keywords
		$this->addElement('text', array(
			'name' => 'page[metakeywords]',
			'title' => 'Keywords',
			'description' => 'Helps search engines classify this page',
			'value' => $page->getMeta('keywords')
		));
		
		// Meta Description
		$this->addElement('textarea', array(
			'name' => 'page[metadescription]',
			'title' => 'Description',
			'description' => 'Text that displays on search engine and social network preview links',
			'value' => $page->getMeta('description')
		));

		// I need to get a list of pages to offer as a dropdown for selecting the "parent" page.
		$f = new ModelFactory('PageModel');
		if($this->get('baseurl')) $f->where('baseurl != ' . $this->get('baseurl'));
		$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');

		$this->addElement('select', array(
			'name' => 'page[parenturl]',
			'title' => 'Parent URL',
			'value' => $page->get('parenturl'),
			'options' => $opts
		));
		
		$this->addElement('access', array(
			'name' => 'page[access]',
			'title' => 'Access Permissions',
			'value' => $page->get('access')
		));

		// @todo Add theme template selection logic
		
		// @todo Add page template selection logic
		
		// Add the insertables.
		//$this->addElement('pageinsertables', array('name' => 'insertables', 'baseurl' => $this->get('baseurl')));
	}

	/**
	 * Save the elements back to the database for the bound base_url.
	 */
	public function save(){
		
		$page = $this->getModel();
		
		//if($this->get('cachedaccess')) $page->set('access', $this->get('cachedaccess'));
		//if($this->get('cachedtitle')) $page->set('title', $this->get('cachedtitle'));
		$page->save();
		
		// Ensure the children have the right baseurl if that changed.
		$els = $this->getElements();
		foreach($els as $e){
			if(!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;
			$e->set('baseurl', $this->get('baseurl'));
		}
		
		// And all the insertables.
		$i = $this->getElementByName('insertables');
		$i->save();
		
		return true;
	}
	
	public function getModel($page = null){
		// Allow linked models.
		if(!$page) $page = new PageModel($this->get('baseurl'));
		
		// Set this model with all the data from the form.
		$page->set('title', $this->getElementByName('page[title]')->get('value'));
		$page->set('rewriteurl', $this->getElementByName('page[rewriteurl]')->get('value'));
		$page->set('parenturl', $this->getElementByName('page[parenturl]')->get('value'));
		$page->setMetas(array(
			'author' => $this->getElementByName('page[metaauthor]')->get('value'),
			'keywords' => $this->getElementByName('page[metakeywords]')->get('value'),
			'description' => $this->getElementByName('page[metadescription]')->get('value')
		));
		$page->set('access', $this->getElementByName('page[access]')->get('value'));
		
		
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
	public function getTemplateName(){
		return null;
	}
	
//	

} // class FormPageInsertables


class FormFileInput extends FormElement{
	private static $_AutoID = 0;
	
	public function __construct($atts = null) {
		// Some defaults
		$this->_attributes = array(
			'class' => 'formelement formfileinput',
			'previewdimensions' => '200x100',
			'browsable' => false,
			'basedir' => '',
		);
		$this->_validattributes = array();
		$this->requiresupload = true;
		
		parent::__construct($atts);
	}
	
	public function render() {
		if(!$this->get('id')){
			// This system requires a valid id.
			++self::$_AutoID;
			$this->set('id', 'formfileinput-' . self::$_AutoID);
		}
		
		if(!$this->get('basedir')){
			throw new Exception('FormFileInput cannot be rendered without a basedir attribute!');
		}
		
		return parent::render();
	}
	
	/**
	 * Get the respective File object for this element.
	 * Use the Core system to ensure compatibility with CDNs.
	 * 
	 * @return File_Backend
	 */
	public function getFile(){
		if($this->get('value')){
			$f = Core::File($this->get('basedir') . '/' . $this->get('value'));
		}
		else{
			$f = Core::File();
		}
		return $f;
	}
	
	public function setValue($value) {
		if($this->get('required') && !$value){
			$this->_error = $this->get('label') . ' is required.';
			return false;
		}
		
		if($value == '_upload_'){
			$n = $this->get('name');
			
			// Because PHP will have different sources depending if the name has [] in it...
			if(strpos($n, '[') !== false){
				$p1 = substr($n, 0, strpos($n, '['));
				$p2 = substr($n, strpos($n, '[') + 1, -1);
				
				if(!isset($_FILES[$p1])){
					$this->_error = 'No file uploaded for ' . $this->get('label');
					return false;
				}
				
				$in = array(
					'name' => $_FILES[$p1]['name'][$p2],
					'type' => $_FILES[$p1]['type'][$p2],
					'tmp_name' => $_FILES[$p1]['tmp_name'][$p2],
					'error' => $_FILES[$p1]['error'][$p2],
					'size' => $_FILES[$p1]['size'][$p2],
				);
			}
			else{
				$in =& $_FILES[$n];
			}
			
			
			if(!isset($in)){
				$this->_error = 'No file uploaded for ' . $this->get('label');
				return false;
			}
			else{
				switch($in['error']){
					case UPLOAD_ERR_OK:
						// Don't do anything, just avoid the default.
						break;
					case UPLOAD_ERR_INI_SIZE:
						$this->_error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
						return false;
					case UPLOAD_ERR_FORM_SIZE:
						$this->_error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ';
						return false;
					default:
						$this->_error = 'An error occured while trying to upload the file for ' . $this->get('label');
						return false;
				}
				
				// Source
				$f = new File_local_backend($in['tmp_name']);
				// Destination
				$nf = Core::File($this->get('basedir') . '/' . $in['name']);
				$f->copyTo($nf);
				
				$value = $nf->getBaseFilename();
			}
		}
		
		$this->_attributes['value'] = $value;
		return true;
	}
}

class FormTimeInput extends FormSelectInput{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Set the options for this input as the times in 15-minute intervals
		// @todo Implement a config option to allow this to be changed to different intervals.
		// @todo also, allow for switching the time view based on a configuration preference.
		$times = array();

		// Default (blank)
		$times[''] = '---';

		for($x=0; $x<24; $x++){
			$hk = $hd = $x;
			if(strlen($hk) == 1) $hk = '0' . $hk;
			if($hd > 12){
				$hd -= 12;
				$ap = 'pm';
			}
			elseif($hd == 0){
				$hd = 12;
				$ap = 'am';
			}
			else{
				$ap = 'am';
			}

			$times["$hk:00"] = "$hd:00 $ap";
			$times["$hk:15"] = "$hd:15 $ap";
			$times["$hk:30"] = "$hd:30 $ap";
			$times["$hk:45"] = "$hd:45 $ap";
		}

		$this->set('options', $times);
	}
}
