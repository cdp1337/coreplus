<?php
/**
 * // enter a good description here
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

class FormGroup{
	protected $_elements;

	protected $_attributes;

	protected $_validattributes = array();

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
		else{
			if(!isset(Form::$Mappings[$element])) $element = 'text'; // Default.

			$this->_elements[] = new Form::$Mappings[$element]($atts);
		}
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

		return $c . (($r)? ' FormRequired' : '') . (($e)? ' FormError' : '');
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

	public function getElements($recursively = true){
		$els = array();
		foreach($this->_elements as $e){
			// Tack on this element, regardless of what it is.
			$els[] = $e;

			// In addition, if it is a group, delve into its children.
			if($recursively && $e instanceof FormGroup) $els = array_merge($els, $e->getElements ($recursively));
		}
		return $els;
	}
	
	/**
	 * Shortcut of getElementByName()
	 * @param string $name 
	 */
	public function getElement($name){
		return $this->getElementByName($name);
	}

	public function getElementByName($name){
		$els = $this->getElements();

		foreach($els as $el){
			if($el->get('name') == $name) return $el;
		}
		
		return false;
	}
}

class FormElement{
	protected $_attributes;

	protected $_error;

	protected $_validattributes = array();

	public function __construct($atts = null){
		$this->_attributes = array();

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
			default:
				return (isset($this->_attributes[$key]))? $this->_attributes[$key] : null;
		}
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

		// @todo Yeah... do the rest of the validation here.....

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
			if(($v = $this->get($k))) $out .= " $k=\"" . str_replace('"', '\\"', $v) . "\"";
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
			preg_match_all('/\[(.*?)\]/', $n, $m);
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
}

class Form extends FormGroup{

	public static $Mappings = array(
		'hidden' => 'FormHiddenInput',
		'pageinsertables' => 'FormPageInsertables',
		'pagemeta' => 'FormPageMeta',
		'password' => 'FormPasswordInput',
		'radio' => 'FormRadioInput',
		'select' => 'FormSelectInput',
		'submit' => 'FormSubmitInput',
		'text' => 'FormTextInput',
		'textarea' => 'FormTextareaInput',
		'wysiwyg' => 'FormTextareaInput',
	);

	/*public function get($key){
		switch(strtolower($key)){
			case 'uniqueid':

			default:
				return parent::get($key);
		}
	}*/

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

	public function  render($part = null) {
		// Slip in the formid tracker to remember this submission.
		if(($part === null || $part == 'body') && $this->get('callsmethod')){
			$e = new FormHiddenInput(array('name' => '___formid', 'value' => $this->get('uniqueid')));
			$this->_elements = array_merge(array($e), $this->_elements);

			// I need to ensure a repeatable but unique id for this form.
			// Essentially when this form is submitted, I need to be able to know that it's the same form upon re-rendering.
			if(!$this->get('uniqueid')){
				$hash = '';
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
		$els = $this->getElements();
		foreach($els as $e){
			if(!$e instanceof FormElement) continue;
			if(!preg_match('/^model\[(.*?)\].*/', $e->get('name'), $matches)) continue;

			$model->set($matches[1], $e->get('value'));
		}
		
		
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
	
	public function loadFrom($src){
		$els = $this->getElements();
		foreach($els as $e){
			if($e instanceof FormGroup) continue;
			// Be sure to clear any errors from the previous page load....
			$e->clearError();
			$e->set('value', $e->lookupValueFrom($src));
			if($e->hasError()) Core::SetMessage($e->getError(), 'error');
		}
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

		if($status === true) Core::Reload();
		else Core::Redirect($status);
	}

	public static function BuildFromModel(Model $model){
		$f = new Form();

		// Add the initial model tracker, will remember which model is attached.
		$f->set('___modelname', get_class($model));

		$new = $model->isnew();

		if(!$new){
			// Save the PKs of this model in the SESSION data so they don't have to be sent to the browser.
			$pks = array();
			foreach($model->getColumnStructure() as $k => $v){
				if($v['primary']) $pks[$k] = $model->get($k);
			}
			$f->set('___modelpks', $pks);
		}

		foreach($model->getColumnStructure() as $k => $v){
			if($new && $v['autoinc']) continue; // Skip the AI column if it doesn't exist.


			if(!$new && $v['primary']) continue; // These are already taken care above in the SESSION data.

			/*if(!$new && $v['primary'] && $k == 'id'){
				// This is a hidden form element.
				$f->addElement('hidden', array('name' => "model[$k]", 'value' => $model->get($k)));
				continue;
			}*/

			if($v['type'] == 'accessstring'){
				// @todo Implement this with the new user system.
				//$f->addElement(new Form)
				$f->addElement('hidden', array('name' => "model[$k]", 'value' => '*'));
				continue;
			}

			if($v['type'] == 'string'){
				$f->addElement('text', array('name' => "model[$k]", 'value' => $model->get($k), 'maxlength' => $v['maxlength'], 'title' => $v['name']));
				continue;
			}

			if($v['type'] == 'text'){
				$f->addElement('textarea', array('name' => "model[$k]", 'value' => $model->get($k), 'maxlength' => $v['maxlength'], 'title' => $v['name']));
				continue;
			}

			//var_dump($v);
		}
		
		// If this model supports Pages, add that too!
		if($model->get('baseurl') && $model->getLink('Page') instanceof PageModel){
			// Tack on the page meta inputs for this page.
			// This will include the rewriteurl, parenturl, theme template and page template.
			$f->addElement('pagemeta', $model->getLink('Page'));
		}

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
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'rows', 'cols', 'style');
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
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'rows', 'cols', 'style');
	}
}


class FormRadioInput extends FormElement{
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formradioinput';
		$this->_validattributes = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}
	
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
			if($i->get('value')) $content = $i->get('value');

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
		$els = $this->getElements();
		foreach($els as $e){
			if(!$e instanceof FormElement) continue;
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
			if(!$this->get('baseurl')) return null;
			
			// Everything is based off the page.
			$page = new PageModel($this->get('baseurl'));
		}
		

		// Rewrite url.
		$this->addElement('text', array('name' => 'page[rewriteurl]', 'title' => 'Rewrite URL', 'value' => $page->get('rewriteurl'), 'description' => 'Starts with a "/", omit ' . ROOT_URL));

		// I need to get a list of pages to offer as a dropdown for selecting the "parent" page.
		$f = new ModelFactory('PageModel');
		$f->where('baseurl != ?', $this->get('baseurl'));
		$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');

		$this->addElement('select', array('name' => 'page[parenturl]', 'title' => 'Parent URL', 'value' => $page->get('parenturl'), 'options' => $opts));

		// @todo Add theme template selection logic
		
		// @todo Add page template selection logic
		
		// Add the insertables.
		$this->addElement('pageinsertables', array('name' => 'insertables', 'baseurl' => $this->get('baseurl')));
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
		$page->set('rewriteurl', $this->getElementByName('page[rewriteurl]')->get('value'));
		$page->set('parenturl', $this->getElementByName('page[parenturl]')->get('value'));
		
		// Add in any insertables too, if they're attached.
		if(($i = $this->getElementByName('insertables'))){
			$els = $i->getElements();
			foreach($els as $e){
				if(!$e instanceof FormElement) continue;
				if(!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;

				$submodel = $page->findLink('Insertable', array('name' => $matches[1]));
				$submodel->set('value', $e->get('value'));
			}
		}
		
		return $page;
	}

	/**
	 * This group does not have a template of its own, should be rendered directly to the form.
	 * @return null
	 */
	public function getTemplateName(){
		return null;
	}

} // class FormPageInsertables
