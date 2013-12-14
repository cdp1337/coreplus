<?php
/**
 * Defines the schema for the Page table
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
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

class PageModel extends Model {

	public static $Schema = array(
		// 2013.07.10 cpowell
		// This has been moved to the beginning of the model so that forms that are built off it will
		// have the title and the corresponding basic data at the top of the list.
		'title' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default'   => null,
			'comment'   => '[Cached] Title of the page',
			'null'      => true,
			'form'      => array(
				'type' => 'text',
				'description' => 'Every page needs a title to accompany it, this should be short but meaningful.',
				'group' => 'Basic',
				'grouptype' => 'tabs',
			),
		),
		'parenturl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => true,
			'form' => array(
				'type' => 'pageparentselect',
				'title' => 'Parent Page',
				'description' => 'The parent this page will appear under in the site breadcrumbs and structure.',
				'group' => 'Meta Information & URL (SEO)',
				'grouptype' => 'tabs',
			),
		),
		'site' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => -1,
			'formtype' => 'system',
			'comment' => 'The site id in multisite mode, (or -1 if global)',
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
			'form' => array(
				'type' => 'system',
			),
		),
		'rewriteurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
			'validation' => array('this', 'validateRewriteURL'),
			'form' => array(
				'title' => 'Page URL',
				'type' => 'pagerewriteurl',
				'description' => 'Starts with a "/", omit the root web dir.',
				'group' => 'Meta Information & URL (SEO)',
				'grouptype' => 'tabs',
			),
		),
		'theme_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
			'comment' => 'Allows the page to define its own theme and widget information.',
			'form' => array(
				'type' => 'pagethemeselect',
				'title' => 'Theme Skin',
				'description' => 'This defines the master theme skin that will be used on this page.',
				'group' => 'Access & Advanced',
				'grouptype' => 'tabs',
			)
		),
		'page_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
			'comment' => 'Allows the specific page template to be overridden.',
			'form' => array(
				'type' => 'pagepageselect',
				'title' => 'Alternative Page Template',
				'group' => 'Basic',
				'grouptype' => 'tabs',
			)
		),
		'last_template' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default'   => null,
			'null'      => true,
			'formtype'  => 'disabled',
			'comment'   => 'The last page template used to render this page, useful in edit pages.',
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'comment' => 'Access string of the page',
			'null' => false,
			'default' => '*',
			'form' => array(
				'type' => 'access',
				'title' => 'Access Permissions',
				'group' => 'Access & Advanced',
				'grouptype' => 'tabs',
			),
		),
		'fuzzy' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => 'If this url is fuzzy or an exact match',
			'null' => false,
			'default' => '0',
			'formtype' => 'system'
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => 'If this page is an administration page',
			'null' => false,
			'default' => '0',
			'formtype' => 'system'
		),
		'admin_group' => array(
			'type' => Model::ATT_TYPE_STRING,
			'comment' => 'Admin pages can be grouped together.  This is the name.',
			'null' => false,
			'default' => '',
			'formtype' => 'disabled',
		),
		'pageviews' => array(
			'type' => Model::ATT_TYPE_INT,
			'formtype' => 'disabled',
			'comment' => 'Number of page views',
			'model_audit_ignore' => true, // Custom key for the component "Model Audit".
		),
		'selectable' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 1,
			'comment' => 'Selectable as a parent url and sitemap page',
			'formtype' => 'disabled',
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('site', 'baseurl'),
		'unique:rewrite_url' => array('site', 'rewriteurl'),
	);

	/**
	 * Set this to the full templatename path to enable resolution of the template and any optional subtemplates.
	 * @var null|string
	 */
	public $templatename = null;

	private $_class;
	private $_method;
	private $_params;

	/**
	 * The View component for this page.
	 * @var View
	 */
	private $_view;

	/**
	 * A cache of rewrite to baseurls to serve as a quick lookup.
	 *
	 * @var array
	 */
	private static $_RewriteCache = null;

	/**
	 * A cache of fuzzy pages, (and their rewrite URLs), to serve as a quick lookup.
	 *
	 * @var array
	 */
	private static $_FuzzyCache = null;


	public function  __construct() {
		$this->_linked = array(
			'Insertable' => array(
				'link' => Model::LINK_HASMANY,
				'on' => 'baseurl'
			),
			'PageMeta' => array(
				'link' => Model::LINK_HASMANY,
				'on' => array('site' => 'site', 'baseurl' => 'baseurl'),
			),
			'RewriteMap' => array(
				'link' => Model::LINK_HASMANY,
				'on' => array('site' => 'site', 'baseurl' => 'baseurl', 'fuzzy' => 'fuzzy'),
			)
		);

		// This system now has a combined primary key.
		// HOWEVER, construction of the model should still be allowed to be performed with simply the baseurl.
		// The first part of the key can be assumed.
		if(func_num_args() == 1){
			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				$site = MultiSiteHelper::GetCurrentSiteID();
			}
			else{
				$site = null;
			}
			$key = func_get_arg(0);
			parent::__construct($site, $key);
			$this->load();
		}
		elseif(func_num_args() == 2){
			$site = func_get_arg(0);
			$key  = func_get_arg(1);
			parent::__construct($site, $key);
		}
		else{
			parent::__construct();
		}
	}


	/**
	 * Get the controller name based on the url.
	 * @return string
	 */
	public function getControllerClass() {
		if (!$this->_class) {
			$a = PageModel::SplitBaseURL($this->get('baseurl'));
			$this->_class = ($a) ? $a['controller'] : null;
		}
		return $this->_class;
	}

	public function getControllerMethod() {
		if (!$this->_method) {
			$a = PageModel::SplitBaseURL($this->get('baseurl'));
			$this->_method = ($a) ? $a['method'] : null;
		}
		return $this->_method;
	}

	public function getParameters() {
		if (!$this->_params) {
			$a = PageModel::SplitBaseURL($this->get('baseurl'));
			$this->_params = ($a) ? $a['parameters'] : array();
		}
		return $this->_params;
	}

	public function getParameter($key) {
		$p = $this->getParameters();
		return (array_key_exists($key, $p)) ? $p[$key] : null;
	}

	public function setParameter($key, $val) {
		$this->_params[$key] = $val;
	}

	public function validateRewriteURL($v) {

		// If it's empty, that's fine, it'll get reset to the baseurl.
		if (!$v) return true;

		// If it's the same as the baseurl, that's fine.
		if ($v == $this->_data['baseurl']) return true;

		if ($v{0} != '/') return "Rewrite URL must start with a '/'";

		if(strpos($v, '#') !== false){
			return 'Invalid Rewrite URL, cannot contain a pound sign (#).';
		}

		// See if the controller segment matches an existing controller.  This cannot happen because
		// that controller would no longer be accessible, example:
		// Blog::View, Blog::Create, etc and /blog.

		$controller = substr($v, 1, ( (strpos($v, '/', 1) !== false) ? strpos($v, '/', 1) : strlen($v)) );
		if($controller && class_exists($controller . 'Controller')){
			return 'Invalid Rewrite URL, "' . $controller . '" is a reserved system name!';
		}

		// Lookup if there is a conflicting URL.
		$ds = Dataset::Init()
			->table('page')
			->count()
			->whereGroup('OR', 'baseurl = ' . $v, 'rewriteurl = ' . $v);

		// If this page exists, I don't want to include this page in the count.
		if ($this->exists()) $ds->where('baseurl != ' . $this->_data['baseurl']);

		// Enterprise/multisite mode anyone?
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$ds->whereGroup('OR', 'site = -1', 'site = ' . MultiSiteHelper::GetCurrentSiteID());
		}

		$ds->execute();

		if ($ds->num_rows > 0) {
			return 'Rewrite URL already taken';
		}

		// All good?
		return true;
	}

	/**
	 * Get the base template name for this page based strictly on its baseurl.
	 *
	 * @return string
	 */
	public function getBaseTemplateName(){
		$t = 'pages/';

		$c = $this->getControllerClass();
		// If it ends with Controller... just drop that bit off.
		// strlen and strrpos execute quicker than preg_match.
		if (strlen($c) - strrpos($c, 'Controller') == 10) {
			// Trim that bit off.
			$c = substr($c, 0, -10);
		}
		$t .= $c . '/';

		$t .= $this->getControllerMethod() . '.tpl';

		return strtolower($t);
	}

	/**
	 * Get the template name, taking the page_template into consideration.
	 *
	 * @return string
	 */
	public function getTemplateName() {
		// can I just cheat and return the last displayed template?
		if($this->get('last_template')){
			return $this->get('last_template');
		}

		$t = $this->getBaseTemplateName();

		// Allow the specific template to be overridden.
		if (($override = $this->get('page_template'))){
			$t = substr($t, 0, -4) . '/' . $override;
		}

		return $t;
	}

	/**
	 *
	 * @return View
	 */
	public function getView() {
		if (!$this->_view) {
			// Create a new data container for use in the transport of the data ultimately.
			$this->_view = new View();
			$this->_populateView();
		}

		return $this->_view;
	}

	/**
	 * Hijack an external view, (presumably from another page),
	 * and load in my stuff over top it.
	 *
	 * This is useful because a single view can be passed around multiple functions,
	 * but it cannot be replaced entirely due to scope reasons.
	 *
	 * @param View $view
	 */
	public function hijackView(View $view) {
		$this->_view = $view;
		$this->_populateView();
	}

	/**
	 * Get an array of the metadata for the metadata.
	 * This is useful for constructing form elements for a given page.
	 *
	 * @return array
	 */
	public function getMetasArray() {

		$fullmetas = array(
			// The SEO title.  This isn't quite a meta tag, but part of that system regardless, so might as well.
			'title' => array(
				'title'       => 'Search-Optimized Title',
				'description' => 'If a value is entered here, the &lt;title&gt; tag of the page will be replaced with this value.  Useful for making the page more indexable by search bots.',
				'type'        => 'text',
				'value'       => (($meta = $this->getMeta('title')) ? $meta->get('meta_value_title') : null),
			),
			// Author
			'author' => array(
				'title'       => 'Author',
				'description' => 'Completely optional, but feel free to include it if relevant',
				'type'        => 'pagemetaauthor',
				'value'       => (($meta = $this->getMeta('author')) ? $meta->get('meta_value_title') : null),
			),
			// The author id
			'authorid' => array(
				'type'        => 'hidden',
				'value'       => (($meta = $this->getMeta('author')) ? $meta->get('meta_value') : null),
			),
			// Keywords, (the human friendly text of them)
			'keywords' => array(
				'title'       => 'Keywords',
				'description' => 'Provides taxonomy data for this page, separate different keywords with a comma.',
				'type'        => 'pagemetakeywords',
				'model'       => $this,
			),
			'description' => array(
				'title'       => 'Description',
				'description' => 'Text that displays on search engine and social network preview links',
				'type'        => 'textarea',
				'value'       => (($meta = $this->getMeta('description')) ? $meta->get('meta_value_title') : null),
			)
		);

		return $fullmetas;
	}

	/**
	 * Get a specific meta tag, or null if it doesn't exist.
	 *
	 * There are a couple exceptions that will return an array of results.  Currently it is simply keywords.
	 *
	 * @param string $name
	 *
	 * @return PageMetaModel | null | array
	 */
	public function getMeta($name) {
		$metas = $this->getLink('PageMeta');
		if($name == 'keywords'){
			$keywords = array();
			foreach($metas as $meta){
				/** @var $meta PageMetaModel */
				if($meta->get('meta_key') == 'keyword') $keywords[] = $meta;
			}
			return $keywords;
		}
		else{
			foreach($metas as $meta){
				/** @var $meta PageMetaModel */
				if($meta->get('meta_key') == $name) return $meta;
			}
		}

		return null;
	}

	/**
	 * Set all meta data for this page
	 *
	 * @param $metaarray array Associated key/value paired array of data to set.
	 *
	 * @return bool
	 */
	public function setMetas($metaarray) {
		if (is_array($metaarray) && count($metaarray)){
			foreach($metaarray as $k => $v){
				$this->setMeta($k, $v);
			}
			return true;
		}

		return false;
	}

	/**
	 * Set a specific meta property or name for this page.
	 *
	 * @param $name string
	 * @param $value string|array
	 */
	public function setMeta($name, $value) {
		// Get, all of the metas for this model!
		$metas = $this->getLink('PageMeta');

		// keywords behave slightly differently here.
		if($name == 'keywords'){
			if(!is_array($value)) $value = array($value => $value);

			// I need to make sure that each value is a key/value pair.
			foreach($value as $valueidx => $valueval){
				if(is_numeric($valueidx)){
					// This will replace any numeric based key with the url version of the value.
					// This may have an odd side effect of transposing numeric values like 2013,
					// but since the url version of a number is just the number itself, it should be ok.
					unset($value[$valueidx]);
					$value[ \Core\str_to_url($valueval) ] = $valueval;
				}
			}

			foreach($metas as $idx => $meta){
				/** @var $meta PageMetaModel */

				// I'm only interested in these!
				if($meta->get('meta_key') != 'keyword') continue;

				if(isset($value[ $meta->get('meta_value') ])){
					// Yay, update the value title
					$meta->set('meta_value_title', $value[ $meta->get('meta_value') ]);
					unset($value[ $meta->get('meta_value') ]);
				}
				else{
					// Nope?  Delete it!
					$meta->delete();
					unset($this->_linked['PageMeta']['records'][$idx]);
				}
			}

			// Any new incoming keywords left?
			foreach($value as $metavalue => $metavaluetitle){
				if(!$metavaluetitle) continue;

				$meta = new PageMetaModel($this->get('site'), $this->get('baseurl'), 'keyword', $metavalue);
				$meta->set('meta_value_title', $metavaluetitle);

				// And append it so it'll get saved on save!
				$this->_linked['PageMeta']['records'][] = $meta;
			}
		}
		elseif($name == 'authorid'){
			// This affects the author tag instead, look for that!
			foreach($metas as $idx => $meta){
				/** @var $meta PageMetaModel */

				// I'm only interested in this one!
				if($meta->get('meta_key') != 'author') continue;

				// It must be the one I'm looking for.
				$meta->set('meta_value', $value);
				return; // :)
			}

			// Doesn't exist?
			$meta = new PageMetaModel($this->get('baseurl'), 'author', $value);

			// And append it so it'll get saved on save!
			$this->_linked['PageMeta']['records'][] = $meta;
		}
		// Default action, one to one!
		else{
			// Look for this key to see if it exists.
			foreach($metas as $idx => $meta){
				/** @var $meta PageMetaModel */

				// I'm only interested in this one!
				if($meta->get('meta_key') != $name) continue;

				// It must be the one I'm looking for.
				if($value){
					// Does it have a value?
					$meta->set('meta_value_title', $value);
				}
				else{
					// It's blank but exists... I can fix that :p
					$meta->delete();
					unset($this->_linked['PageMeta']['records'][$idx]);
				}
				return; // :)
			}

			// Doesn't exist?
			if($value){
				$meta = new PageMetaModel($this->get('site'), $this->get('baseurl'), $name, '');
				$meta->set('meta_value_title', $value);

				// And append it so it'll get saved on save!
				$this->_linked['PageMeta']['records'][] = $meta;
			}
		}
	}

	/**
	 * Set the insertable's value for this page.
	 * Will automatically create the InsertableModel and attach it to this page if it doesn't exist.
	 *
	 * Please note, will NOT save the models automatically.
	 *
	 * @param $name
	 * @param $value
	 */
	public function setInsertable($name, $value){
		// Get, all of the insertables for this model!
		$insertables = $this->getLink('Insertable');

		foreach($insertables as $ins){
			/** @var $ins InsertableModel */

			// Look for this key to see if it exists.

			// I'm only interested in this one!
			if($ins->get('name') == $name){
				$ins->set('value', $value);
				return; // :)
			}
		}

		// Doesn't exist?
		$ins = new InsertableModel($this->get('site'), $this->get('baseurl'), $name);
		$ins->set('value', $value);

		// And append it so it'll get saved on save!
		$this->_linked['Insertable']['records'][] = $ins;
	}

	/**
	 * Get the rewrite URLs of this page as a "\n" separated string, suitable for forms.
	 *
	 * @return string
	 */
	public function getRewriteURLs(){
		$rewrites = $this->getLink('RewriteMap');

		$out = '';
		foreach($rewrites as $r){
			/** @var $r RewriteMapModel */
			$v = $r->get('rewriteurl');
			if($v{0} == '/') $out .= ROOT_URL . substr($v, 1) . "\n";
			else $out .= $v . "\n";
		}

		return trim($out);
	}

	/**
	 * Set the available rewrites for this page.
	 * Will automatically create the RewriteMapModel and attach it to this page if it doesn't exist.
	 *
	 * Please note, will NOT save the models automatically.
	 *
	 * @param string|array $urls newline, comma, or pipe delimited string of urls, also arrays are accepted.
	 */
	public function setRewriteURLs($urls){
		// make sure the URLs are in a format I can actually use.
		if(!is_array($urls)){
			$string = $urls;
			$urls = array();

			// Convert the various accepted delimiters to a standard single one so I can explode easily.
			$string = str_replace([',', '|', "\r"], "\n", $string);
			$urls = array_map('trim', explode("\n", $string));
		}

		// Next step now that I have an array, I need to go through each one and standardize it.
		// ie: if ROOT_URL is present, I need to trim that off since it's redundant.
		// Same thing for any other URL in fact....
		// The mapping doesn't care about the hostname.
		foreach($urls as $k => $v){
			if(!$v){
				unset($urls[$k]);
			}
			elseif(strpos($v, ROOT_URL_NOSSL) === 0){
				$urls[$k] = '/' . substr($v, strlen(ROOT_URL_NOSSL));
			}
			elseif(strpos($v, ROOT_URL_SSL) === 0){
				$urls[$k] = '/' . substr($v, strlen(ROOT_URL_SSL));
			}
			elseif(strpos($v, '://') !== false){
				// Trim whatever is before the ://
				$v = substr($v, strpos($v, '://') + 3);
				// Trim whatever is left after the first '/'.t
				$urls[$k] = substr($v, strpos($v, '/'));
			}
			else{
				// It just needs to start with a '/'.
				if($v{0} != '/'){
					$urls[$k] = '/' . $v;
				}
			}
		}


		// Get, all of the rewrites for this model currently.
		// This is because if there is a deletion, I want to be able to delete the old link.
		$rewrites = $this->getLink('RewriteMap');

		// Queue up any that need to be deleted first.
		foreach($rewrites as $rewrite){
			/** @var $rewrite RewriteMapModel */
			if(!in_array($rewrite->get('rewriteurl'), $urls)){
				$this->deleteLink($rewrite);
			}
		}

		// Now I can add any new one.

		foreach($urls as $url){
			// Since RewriteURLs can be set from anywhere and they're pretty in-flux,
			// I need to lookup this url and force it to be what I need it to be.
			$rewrite = RewriteMapModel::Find(['rewriteurl = ' . $url], 1);
			if(!$rewrite){
				$rewrite = new RewriteMapModel();
				$rewrite->set('rewriteurl', $url);
			}

			$this->setLink('RewriteMap', $rewrite);
		}
	}

	/**
	 * Set properties on this model from a form object, optionally with a specific prefix.
	 *
	 * @param Form        $form   Form object to pull data from
	 * @param string|null $prefix Prefix that all keys should be matched to, (optional)
	 */
	public function setFromForm(Form $form, $prefix = null){

		// This will take care of all the standard elements.
		parent::setFromForm($form, $prefix);

		// And this will take care of the rewrites
		$rewrites = $form->getElement($prefix . '[rewrites]')->get('value');
		$this->setRewriteURLs($rewrites);

		// And this will take care of the meta elements.
		$baselen = strlen($prefix . '[metas]');
		foreach($form->getElements(true, false) as $el){
			$name = $el->get('name');
			if(strpos($name, $prefix . '[metas]') === 0){
				$key = substr($name, $baselen+1, -1);

				$this->setMeta($key, $el->get('value'));
			}
		}

		// And this will take care of the insertable elements.
		$baselen = strlen($prefix . '[insertables]');
		foreach($form->getElements(true, false) as $el){
			$name = $el->get('name');
			if(strpos($name, $prefix . '[insertables]') === 0){
				$key = substr($name, $baselen+1, -1);

				$this->setInsertable($key, $el->get('value'));
			}
		}
	}

	public function setToFormElement($key, FormElement $element){
		if($key == 'page_template'){
			// Make sure to set the element's templatename.
			$element->set('templatename', $this->getBaseTemplateName());
		}
	}

	/**
	 * Method that is called on the model after "addModel" is called on a form.
	 *
	 * Any special logic such as adding custom elements from the model can be done here, simply extend this method and add logic as necessary.
	 *
	 * @param Form   $form    The form itself.
	 * @param string $prefix  The form prefix to use, ie: "page" or "model", etc.
	 */
	public function addToFormPost(Form $form, $prefix){
		// Get the groups that these additional elements will tack onto,
		// and create them if necessary.
		$metasgroupname = 'Meta Information & URL (SEO)';
		$insertablesgroupname = 'Basic';

		$metasgroup = $form->getElement($metasgroupname);
		if(!$metasgroup){
			$metasgroup = new FormGroup(array('title' => $metasgroupname, 'name' => $metasgroupname));
			$form->addElement($metasgroup);
		}

		$insertablesgroup = $form->getElement($insertablesgroupname);
		if(!$insertablesgroup){
			$insertablesgroup = new FormGroup(array('title' => $insertablesgroupname, 'name' => $insertablesgroupname));
			$form->addElement($insertablesgroup);
		}

		// I need to add the rewrite options, (I need to get them too).
		$metasgroup->addElement(
			'textarea',
			[
				'name' => $prefix . '[rewrites]',
				'title' => 'Rewrite Aliases',
				'value' => $this->getRewriteURLs(),
				'description' => 'Enter rewrite aliases that point to this page, one per line.  You may use the fully resolved path or simply the part after the ".com".',
			]
		);

		// I need to add the pagemetas!
		foreach($this->getMetasArray() as $key => $dat){
			$type = $dat['type'];
			$dat['name'] = $prefix . '[metas][' . $key . ']';

			$metasgroup->addElement($type, $dat);
		}

		// And the page insertables.
		$tpl = Core\Templates\Template::Factory($this->getTemplateName());
		if($tpl){
			foreach($tpl->getInsertables() as $key => $dat){
				$type = $dat['type'];
				$dat['name'] = $prefix . '[insertables][' . $key . ']';

				// This insertable may already have content from the database... if so I want to pull that!
				$i = InsertableModel::Construct($this->get('site'), $this->get('baseurl'), $key);
				if ($i->get('value') !== null){
					$dat['value'] = $i->get('value');
				}

				$dat['class'] = 'insertable';

				$insertablesgroup->addElement($type, $dat);
			}
		}
	}

	public function getResolvedURL() {

		// If enterprise // multisite mode is enabled and this page model is NOT the current site...
		// I need to lookup THAT site's root url and use that instead.
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			if($this->get('site') == -1){
				// "-1" pages exist on every site as a relative page.
				$base = ROOT_URL;
			}
			elseif($this->get('site') != MultiSiteHelper::GetCurrentSiteID()){
				// The site isn't the same as the current.... I need to do a little bit of work.
				// Please note, this only happens about 0.5% of the time.
				// primarily in the sitemap plugin :/
				$site = MultiSiteModel::Construct($this->get('site'));
				$base = 'http://' . $site->get('url') . '/';
			}
			else{
				// Who cares in this case, just use the root url.
				$base = ROOT_URL;
			}
		}
		else{
			// Enterprise/MM isn't enabled... no biggie!
			$base = ROOT_URL;
		}

		if ($this->exists()) {
			return $base . substr($this->get('rewriteurl'), 1);
		}
		else {
			$s = self::SplitBaseURL($this->get('baseurl'));
			return $base . substr($s['baseurl'], 1);
		}
	}


	/**
	 *
	 * @return View
	 */
	public function execute() {

		$transport = $this->getView();

		// I need a valid class/method pair!
		$c = $this->getControllerClass();
		$m = $this->getControllerMethod();
		if (!($c && $m)) {
			$transport->error = View::ERROR_NOTFOUND;
			return $transport;
		}

		// Check if this Controller has an AccessString set statically.
		// This allows the method to be skipped entirely.
		if ($c::$AccessString !== null) {
			$transport->access = $c::$AccessString;

			if (!Core::User()->checkAccess($c::$AccessString)) {
				$transport->error = View::ERROR_ACCESSDENIED;
				return $transport;
			}
		}

		// Populate the transport view object with some preliminary information
		// if the page exists and has it.
		// This information can get overwrote in the view method if requested.
		if ($this->exists()) {
			$transport->title  = $this->get('title');
			$transport->access = $this->get('access');
		}

		$r = call_user_func(array($c, $m), $transport);

		// Multiple return values can be accepted.
		// nothing, an error code, or the page.
		if ($r === null) {
			// No return needed, assume this same object.
			$r = $transport;
		}
		elseif (is_numeric($r)) {
			// Should be a valid error code.
			$transport->error = $r;
		}

		if ($transport->error == View::ERROR_NOERROR && $this->exists()) {
			// This information is cached.
			$this->set('title', $transport->title);
			$this->set('access', $transport->access);

			$this->save();
		}

		return $transport;
	}

	public function  save() {
		// Ensure some helper variables are set.
		if (!$this->get('rewriteurl')) $this->set('rewriteurl', $this->get('baseurl'));

		// If the rewrite URL was changed, I need to invalidate the cache.
		// This is because many components that may change the url, will immediately want to reload to that new url.
		if(!isset($this->_datainit['rewriteurl'])) $this->_datainit['rewriteurl'] = null;
		if($this->_data['rewriteurl'] != $this->_datainit['rewriteurl']){
			self::$_FuzzyCache = null;
			self::$_RewriteCache = null;
		}

		// If this model existed before and the URL has changed, update the lookup table!
		// This will act as a basis of rewrite rules for changed URLs, allowing users to change
		// their pages rewriteurls without adversely affecting inbounding links.
		if($this->exists() && $this->_data['rewriteurl'] != $this->_datainit['rewriteurl']){
			// I don't care if the map existed, or was linked to something else...
			// All I need to do is ensure that it will redirect to the new URL.
			$map = new RewriteMapModel($this->_datainit['rewriteurl']);
			$map->set('site', $this->_data['site']);
			$map->set('baseurl', $this->_data['baseurl']);
			$map->set('fuzzy', $this->_data['fuzzy']);
			$map->save();
		}

		return parent::save();
	}

	public function getParentTree() {
		// Allow pages that do not exist to have a bit of "extended" logic for determining the breadcrumbs.
		if (!$this->exists()) {
			// Do a bit of custom logic here.

			$m = strtolower($this->getControllerMethod());
			$b = strtolower($this->get('baseurl'));

			// If the page is currently Edit and there is a View... handle that instance.
			if ($m == 'edit' && method_exists($this->getControllerClass(), 'view')) {
				$p = PageModel::Construct(str_replace('/edit/', '/view/', $b));
				if ($p->exists()) {
					// I need the array merge because getParentTree only returns << parents >>.
					return array_merge($p->getParentTree(), array($p));
				}
			}

			// If the page is currently Delete and there is a View... handle that instance.
			if ($m == 'delete' && method_exists($this->getControllerClass(), 'view')) {
				$p = PageModel::Construct(str_replace('/delete/', '/view/', $b));
				// Only try to call the script if it exists.
				if ($p->exists()) {
					// I need the array merge because getParentTree only returns << parents >>.
					return array_merge($p->getParentTree(), array($p));
				}
			}
		}

		// _getParentTree will go the long way about returning results, and may return blank / invalid ones.
		// If so, clean those results.
		$ret = array();
		foreach ($this->_getParentTree() as $p) {
			if ($p->exists() || $p->get('title')) {
				$ret[] = $p;
			}
		}

		return $ret;
		//return $this->_getParentTree();
	}

	private function _getParentTree($antiinfiniteloopcounter = 5) {
		if ($antiinfiniteloopcounter <= 0) return array();
		$p = false;
//echo "Running _getParentTree for " . $this->get('baseurl') . '<br/>';
//echo '<pre>'; debug_print_backtrace();
		if (!$this->exists()) {
			// See if this page is maybe a child of another page... ie: /Blah/view/this
			// might be a child page of /Blah
			// This section will run up the stack of GET parameters until it either finds
			// something or nothing.
			// Yes, I know this can be time consuming, but if you have a better way, please optimize it.

			// Lookup something, just to ensure it's in the cache.
			self::_LookupUrl('/');

			$url = strtolower($this->get('baseurl'));
			do {
				$url = substr($url, 0, strrpos($url, '/'));
//var_dump($url, self::$_RewriteCache[$url]);
				// To optimize this part, use the built-in cache of this object
				// instead of querying the database.
				// This works because the above statement self::_LookupUrl('/'); will
				// load in every valid baseurl in the database into an array.
				// therefore, obviously if a key exists in that array, the page exists! :)
				if (isset(self::$_RewriteCache[$url])) {
					$url = self::$_RewriteCache[$url];
				}

				//$p = new PageModel($url);
				// The new static Construct offers caching :)
				$p = PageModel::Construct($url);

				return array_merge($p->_getParentTree(--$antiinfiniteloopcounter), array($p));

				/* Remind me why this was ever enabled...
				// Fuzzy pages that do not have a parent url specifically set should not propagate up.
				if ($p->get('fuzzy') && !$p->get('parenturl')) {
					//echo "returning from #1<hr/>";
					//return array($p);
					return array();
				}
				else {
					//echo "returning from #2<hr/>";
					return array_merge($p->_getParentTree(--$antiinfiniteloopcounter), array($p));
				}
				*/
				//$pagedat = self::_LookupReverseUrl($url);
			}
			while ($url);
		}
//echo '<pre>'; debug_print_backtrace(); var_dump($this); die();
		// If this page does not have a parent, BUT is marked as an admin page..
		// /admin is automatically prefixed.
		// (unless the current page *is* /admin.... then it can be skipped.
		if (!$this->get('parenturl') && $this->get('admin') && strtolower($this->get('baseurl')) != '/admin') {
			$url = '/admin';
			if (isset(self::$_RewriteCache[$url])) {
				//$p = new PageModel($url);
				// The new static Construct offers caching :)
				$p = PageModel::Construct($url);
			}
			return $p ? array($p) : array();
		}

		// If this page does not have a parent, simply return a blank array.
		if (!$this->get('parenturl')) return array();

		//$p = new PageModel($this->get('parenturl'));
		// The new static Construct offers caching :)
		$p = PageModel::Construct($this->get('parenturl'));

		return array_merge($p->_getParentTree(--$antiinfiniteloopcounter), array($p));
	}

	private function _populateView() {
		// Transpose some useful data for it.
		$this->_view->error = View::ERROR_NOERROR;
		$this->_view->baseurl = $this->get('baseurl');
		$this->_view->setParameters($this->getParameters());
		$this->_view->templatename = $this->getTemplateName();
		$this->_view->mastertemplate = ($this->get('template')) ? $this->get('template') : ConfigHandler::Get('/theme/default_template');

		$this->_view->setBreadcrumbs($this->getParentTree());
	}


	/****************** Helper Static Functions **************************/

	/**
	 * Split a base url into its corresponding parts, controller method and parameters.
	 * Also supports the rewriteurl.
	 *
	 * @param string $base
	 *
	 * @return array
	 */
	public static function SplitBaseURL($base) {

		if (!$base) return null;

		// Default ctype.
		$ctype = 'text/html';

		// Update the cache!
		self::_LookupUrl(null);

		// In order to do the match, the incoming url needs to be all lowercase!
		$base = strtolower($base);

		// so now I can translate that rewriteurl to the baseurl.
		if (isset(self::$_RewriteCache[$base])) {
			$base = self::$_RewriteCache[$base];
		} // or find a fuzzy page if there is one.
		// remember, fuzzy pages are meant to act as a sort of directory placeholder.
		else {
			$try = $base;
			//$fuzzyfound = false;
			while($try != '' && $try != '/') {
				if(isset(self::$_FuzzyCache[$try])) {
					// The fuzzy page must have the requested arguments, they just need to be tacked onto the end of the base.
					$base = self::$_FuzzyCache[$try] . substr($base, strlen($try));
					//		$fuzzyfound = true;
					break;
				}
				elseif(in_array($try, self::$_FuzzyCache)) {
					$base = self::$_FuzzyCache[array_search($try, self::$_FuzzyCache)] . substr($base, strlen($try));
					//		$fuzzyfound = true;
					break;
				}
				$try = substr($try, 0, strrpos($try, '/'));
			}

			// This is a weird exception for when the root page is set to fuzzy.
			// It's not checked in the above loop because it thinks the check is over.
			//if(!$fuzzyfound && isset(self::$_FuzzyCache['/'])){
			//	$base = self::$_FuzzyCache['/'] . $base;
			//}
		}

		// Trim off both beginning and trailing slashes.
		$base = trim($base, '/');


		$args = null;
		// Support additional arguments
		if (($qpos = strpos($base, '?')) !== false) {
			$argstring = substr($base, $qpos + 1);
			preg_match_all('/([^=&]*)={0,1}([^&]*)/', $argstring, $matches);
			$args = array();
			foreach ($matches[1] as $k => $v) {
				if (!$v) continue;
				$args[$v] = $matches[2][$k];
			}
			$base = substr($base, 0, $qpos);
		}

		// Logic for the Controller.
		$posofslash = strpos($base, '/');

		if ($posofslash) $controller = substr($base, 0, $posofslash);
		else $controller = $base;

		// Preferred way of handling controller names.
		if (class_exists($controller . 'Controller')) {
			switch (true) {
				// 2.1 API
				case is_subclass_of($controller . 'Controller', 'Controller_2_1'):
					// 1.0 API
				case is_subclass_of($controller . 'Controller', 'Controller'):
					$controller = $controller . 'Controller';
					break;
				default:
					// Not a valid controller
					return null;
			}
		}
		// Not quite preferred way, but still works.
		elseif (class_exists($controller)) {
			switch (true) {
				// 2.1 API
				case is_subclass_of($controller, 'Controller_2_1'):
					// 1.0 API
				case is_subclass_of($controller, 'Controller'):
					$controller = $controller;
					break;
				default:
					// Not a valid controller
					return null;
			}
		}
		else {
			// Not even found!
			return null;
		}


		// Trim the base.
		if ($posofslash !== false) $base = substr($base, $posofslash + 1);
		else $base = false;

		// Logic for the Method.
		//if(substr_count($base, '/') >= 1){
		if ($base) {

			$posofslash = strpos($base, '/');

			// The method can be extended.
			// This means that a method can be in the format of Sites/Edit, which should resolve to Sites_Edit.
			// This only taks effect if the method exists on the controller.
			if ($posofslash) {
				$method = str_replace('/', '_', $base);
				while (!method_exists($controller, $method) && strpos($method, '_')) {
					$method = substr($method, 0, strrpos($method, '_'));
				}
			}
			else {
				$method = $base;
			}

			// Now trim the base again based on the length of the method.
			$base = substr($base, strlen($method) + 1);
		}
		else {
			// The controller may have an "Index" controller.  That doesn't need to be explictly called.
			$method = 'Index';
		}

		// If there was a content type requested, (.something), then trim that off too!
		if(strpos($method, '.') !== false){
			$ctype = \Core\Filestore\extension_to_mimetype(substr($method, strpos($method, '.') + 1));

			// Invalid mimetype?  Default to an HTML file.
			if(!$ctype){
				$ctype = 'text/html';
			}

			$method = substr($method, 0, strpos($method, '.'));
		}

		// One last check that the method exists, (because there's only 1 scenario that checks above)
		if (!method_exists($controller, $method)) {
			return null;
		}


		// Provide some logic for security.
		// Keep any method starting with a '_' private by preventing
		// direct access from the browser.
		if ($method{0} == '_') return null;


		// Logic for the parameters.
		$params = ($base !== false) ? explode('/', $base) : null;


		// Build these onto a base for a standardized callable URL.
		$baseurl = '/' . ((strpos($controller, 'Controller') == strlen($controller) - 10) ? substr($controller, 0, -10) : $controller);
		// No need to add a method if it's the index.
		if (!($method == 'Index' && !$params)) $baseurl .= '/' . str_replace('_', '/', $method);
		$baseurl .= ($params) ? '/' . implode('/', $params) : '';
		// Rewrite URL may be useful too!
		$rewriteurl = self::_LookupReverseUrl($baseurl);

		// Keep the original mimetype extension if set.
		if($ctype != 'text/html'){
			$rewriteurl .= '.' . \Core\Filestore\mimetype_to_extension($ctype);
		}

		// Keep the arguments on the rewrite version.
		if ($args) {
			$rewriteurl .= '?' . $argstring;
			if ($params) $params = array_merge($params, $args);
			else $params = $args;
		}

		// Tack on the "arguments" too, these are 

		return array('controller' => $controller,
		             'method' => $method,
		             'parameters' => $params,
		             'baseurl' => $baseurl,
			'rewriteurl' => $rewriteurl,
			'ctype'      => $ctype,
		);
	}

	/**
	 * Lookup a url in the rewrite cache.  Useful for initial rewrite -> base conversions
	 *
	 * @param type $url
	 */
	private static function _LookupUrl($url = null) {
		if (self::$_RewriteCache === null) {
			$s = new Dataset();
			$s->select('rewriteurl, baseurl, fuzzy');
			$s->table(DB_PREFIX . 'page');

			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				$g = new DatasetWhereClause();
				$g->setSeparator('OR');
				$g->addWhere('site = -1');
				$g->addWhere('site = ' . MultiSiteHelper::GetCurrentSiteID());
				$s->where($g);
			}

			$rs = $s->execute();
			self::$_RewriteCache = array();
			self::$_FuzzyCache = array();

			foreach ($rs as $row) {
				self::$_RewriteCache[strtolower($row['rewriteurl'])] = strtolower($row['baseurl']);
				if ($row['fuzzy']) self::$_FuzzyCache[strtolower($row['rewriteurl'])] = strtolower($row['baseurl']);
			}
		}

		if ($url === null) return; // maybe this was just called to update the local rewrite and fuzzy caches.
		return (isset(self::$_RewriteCache[$url])) ? self::$_RewriteCache[$url] : $url;
	}

	/**
	 * Lookup the rewrite url for a given url.
	 *
	 * @param type $url
	 */
	private static function _LookupReverseUrl($url) {
		// Lookup something, just to ensure it's in the cache.
		self::_LookupUrl(null);

		$url = strtolower($url);

		// See if it directly matches a cached page
		if (($key = array_search($url, self::$_RewriteCache)) !== false) {
			return $key;
		}

		// Else try to look it up in the fuzzy pages.
		$try = $url;
		while ($try != '' && $try != '/') {
			if (in_array($try, self::$_FuzzyCache)) {
				$url = array_search($try, self::$_FuzzyCache) . substr($url, strlen($try));
				return $url;
			}
			$try = substr($try, 0, strrpos($try, '/'));
		}

		// Nope, just return the URL then :/
		return $url;
	}

	/**
	 * Get all pages, (with an optional where clause), as a valid option array
	 *
	 * This array contains key of "baseurl", value of "parent &raquo; title ( url )"
	 * that is directly pluggable into the Form system or a manual foreach loop.
	 *
	 * @param mixed $where Either a ModelFactory (usually with custom-crafted where clauses),
	 *                     or a string of the where clause
	 *                     or false to omit the where clause.
	 * @param mixed $blanktext The text to include with the blank entry
	 *                         If false, no blank field is included.
	 *
	 * @return array
	 */
	public static function GetPagesAsOptions($where = false, $blanktext = false) {
		if ($where instanceof ModelFactory) {
			$f = $where;
		}
		elseif (!$where) {
			$f = new ModelFactory('PageModel');
		}
		else {
			$f = new ModelFactory('PageModel');
			$f->where($where);
		}

		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$g = new DatasetWhereClause();
			$g->setSeparator('OR');
			$g->addWhere('site = -1');
			$g->addWhere('site = ' . MultiSiteHelper::GetCurrentSiteID());
			$f->where($g);
		}

		// They must be selectable!
		$f->where('selectable = 1');

		// Get the pages
		$pages = $f->get();

		// Assemble a list of page titles for quick reference.
		//$titles = array();
		//foreach($pages as $p){
		//	$titles[$p->get('baseurl')] = $p->get('title');
		//}

		// Now I can assemble the list of options with useful labels
		$opts = array();
		foreach ($pages as $p) {
			$baseurl = strtolower($p->get('baseurl'));

			$t = '';
			foreach ($p->getParentTree() as $subp) {
				$t .= $subp->get('title') . ' &raquo; ';
			}
			$t .= $p->get('title');
			$t .= ' ( ' . $p->get('rewriteurl') . ' )';
			$tlen = strlen(html_entity_decode($t));
			if($tlen > 80){
				// This needs to take into account for the html characters.
				$t = substr($t, 0, (77 + (strlen($t) - $tlen)) ) . '&hellip;';
			}
			$opts[$baseurl] = $t;
		}

		// Sort'em
		asort($opts);

		// Default should always be at the top (if requested).
		if ($blanktext) $opts = array_merge(array("" => $blanktext), $opts);

		// And here ya go!
		return $opts;
	}
}
