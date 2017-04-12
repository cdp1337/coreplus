<?php
/**
 * Defines the schema for the Page table
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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
				'type' => 'select',
				'title' => 'Parent Page',
				'description' => 'The parent this page will appear under in the site breadcrumbs and structure.',
				'group' => 'Meta Information & URL (SEO)',
				'grouptype' => 'tabs',
				'source' => 'this::_getParentsAsOptions',
			),
		),
		'site' => array(
			'type' => Model::ATT_TYPE_SITE,
			'default' => -1,
			'form' => [
				'type' => 'system',
				'group' => 'Access & Advanced',
				'grouptype' => 'tabs',
				'description' => 'Please note, changing the site ID on an existing page may result in loss of data or unexpected results.',
			],
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
		'editurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => '',
			'required' => false,
			'null' => false,
			'form' => array(
				'type' => 'disabled',
			),
			'comment' => 'The edit URL for this page, set by the creating application.',
		),
		'deleteurl' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default'   => '',
			'required'  => false,
			'null'      => false,
			'form' => array(
				'type' => 'disabled',
			),
			'comment'   => 'The URL to perform the POST on to delete this page',
		),
		// Added on 2014.02 to keep track of which pages need to be cleaned up on component removal.
		// Do not enforce this until all components support it.
		'component'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 48,
			'required'  => false,
			'default'   => '',
			'null'      => false,
			'form' => array(
				'type' => 'disabled',
			),
			'comment'   => 'The component that registered this page, useful for uninstalling and cleanups',
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
		'expires' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => 3600,
			'form' => [
				'title' => 'Cacheable / Expires',
				'type' => 'select',
				'options' => [
					'0'       => 'No Cache Allowed',
					'30'      => '30 seconds',
					'60'      => '1 minute',
					'120'     => '2 minutes',
					'300'     => '5 minutes',
					'600'     => '10 minutes',
					'1800'    => '30 minutes',
					'3600'    => '1 hour',
					'7200'    => '2 hours',
					'14400'   => '4 hours',
					'21600'   => '6 hours',
					'28800'   => '8 hours',
					'43200'   => '12 hours',
					'64800'   => '18 hours',
					'86400'   => '24 hours',
					'172800'  => '2 days',
					'604800'  => '1 week',
					'2462400' => '1 month',
				],
				'description' => 'Amount of time this page has a valid cache for, set to 0 to completely disable.
					This cache only applies to guest users and bots.',
				'group' => 'Access & Advanced',
				'grouptype' => 'tabs',
			],
			'formatter' => '\Core\Formatter\GeneralFormatter::TimeDuration',
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
			'formatter' => '\Core\Formatter\GeneralFormatter::AccessString',
		),
		'password_protected' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'comment' => 'Password or phrase to protect this page',
			'null' => false,
			'default' => '',
			'form' => array(
				'type' => 'text',
				'title' => 'Password',
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
			'default' => 0,
			'comment' => 'Number of page views',
			'model_audit_ignore' => true, // Custom key for the component "Model Audit".
		),
		'selectable' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 1,
			'comment' => 'Selectable as a parent url',
			'formtype' => 'disabled',
		),
		'indexable' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 1,
			'comment' => 'Page is displayed on the sitemap, search, and search crawlers',
			'form' => [
				//'type' => 'checkbox',
				'description' => 'Set to No if you do not want this page to be listed in search results.',
				'group' => 'Meta Information & URL (SEO)',
				'grouptype' => 'tabs',
			],
		),
		'popularity' => array(
			'type' => Model::ATT_TYPE_FLOAT,
			'default' => 0.000,
			'precision' => '10,8',
			'comment' => 'Cache of the popularity score of this page',
			'formtype' => 'disabled',
		),
		'published_status'      => array(
			'type'    => Model::ATT_TYPE_ENUM,
			'options' => array('published', 'draft'),
			'default' => 'published',
			'form' => array(
				'title' => 'Published Status',
				'description' => 'Set this to "draft" to make it visible to editors and admins
					only.  Useful for saving a page without releasing it to public users.',
				'group' => 'Publish Settings',
				'grouptype' => 'tabs',
			),
			'formatter' => ['this', 'getPublishedStatus'],
		),
		'published' => array(
			'type' => Model::ATT_TYPE_INT,
			'form' => array(
				'title' => 'Published Date',
				'type' => 'datetime',
				'description' => 'Set this field to a desired date/time to mark the page to be published at that specific date and time.  If left blank, the current date and time are set automatically.  This CAN be set this to a future date to have the page to be published at that time.',
				'group' => 'Publish Settings',
				'grouptype' => 'tabs',
			),
			'comment' => 'The published date',
			'formatter' => '\Core\Formatter\GeneralFormatter::DateStringSD',
		),
		'published_expires' => array(
			'type' => Model::ATT_TYPE_STRING,
			'null' => true,
			'default' => null,
			'form' => array(
				'title' => 'Publish Expires Date',
				'type' => 'datetime',
				'description' => 'Set to a future date/time to un-publish this page automatically at that specific date and time.',
				'group' => 'Publish Settings',
				'grouptype' => 'tabs',
				'datetimepicker_dateformat' => 'yy-mm-dd',
				'datetimepicker_timeformat' => 'HH:mm',
			),
			'formatter' => '\Core\Formatter\GeneralFormatter::DateStringSDT',
		),
		'body' => array(
			'type'      => Model::ATT_TYPE_TEXT,
			'default'   => '',
			'comment'   => '[Cached] Body content of this page',
			'null'      => false,
			'form'      => array(
				'type' => 'disabled',
			),
		),
		'seotitle' => [
			'type' => Model::ATT_TYPE_META,
			'formatter' => ['this', 'getSEOTitle'],
		],
		'teaser' => [
			'type' => Model::ATT_TYPE_META,
			'formatter' => ['this', 'getTeaser'],
		],
	);

	public static $Indexes = array(
		'primary'            => ['site', 'baseurl'],
		'unique:rewrite_url' => ['site', 'rewriteurl'],
		'baseurlidx'         => ['baseurl'],
		'adminidx'           => ['admin'],
		'rewritefuzzy'       => ['rewriteurl', 'fuzzy'],
		'baseurlfuzzy'       => ['baseurl', 'fuzzy'],
	);

	public static $HasCreated = true;
	public static $HasUpdated = true;
	public static $HasSearch  = true;

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

	/** @var null|array Cache of Rewrite URL -> Base URL (each base can have multiple rewrites) */
	private static $_RewriteCache = null;

	/** @var null|array A Cache of fuzzy pages, (and their rewrite URLs), to serve as a quick lookup. */
	private static $_FuzzyCache = null;
	
	/** @var null|array Cache of Base URL -> Primary Rewrite URL */
	private static $_BaseCache = null;


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
			if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
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

	/**
	 * Get the logo URL for this page, if available.
	 * 
	 * @return null|string
	 */
	public function getLogoURL(){
		$logo = $this->getLogo();
		return $logo !== null ? $logo->getPreviewURL('24x24') : null;
	}
	
	/**
	 * Get the logo File for this page, if available.
	 * 
	 * @return null|\Core\Filestore\File
	 */
	public function getLogo(){
		if(($img = $this->getImage())){
			// Best way, this page has a main image set!
			return $img;
		}
		elseif($this->get('component')){
			// Does this page have a registered component?
			// If so, I can pull that logo!
			$c = Core::GetComponent($this->get('component'));
			if(($logo = $c->getLogo())){
				return $logo;
			}
		}
		
		return null;
	}

	public function setParameter($key, $val) {
		$this->_params[$key] = $val;
	}

	public function validateRewriteURL($v) {

		// If it's empty, that's fine, it'll get reset to the baseurl.
		if (!$v) return true;

		// If it's the same as the baseurl, that's fine.
		if ($v == $this->_columns['baseurl']->value) return true;

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
		$ds = Core\Datamodel\Dataset::Init()
			->table('page')
			->select('*')
			->whereGroup('OR', 'baseurl = ' . $v, 'rewriteurl = ' . $v);

		// If this page exists, I don't want to include this page in the count.
		if ($this->exists()){
			$ds->where('baseurl != ' . parent::get('baseurl'));
		}

		// Enterprise/multisite mode anyone?
		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			$ds->whereGroup('OR', 'site = -1', 'site = ' . MultiSiteHelper::GetCurrentSiteID());
		}

		$ds->execute();

		if ($ds->num_rows > 0) {
			if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
				foreach($ds as $row){
					if($row['site'] == $this->get('site') || $row['site'] == '-1'){
						// Only trigger the URL taken error if it's on the same site or it's a global page.
						return 'Rewrite URL already taken';
					}
				}
			}
			else{
				return 'Rewrite URL already taken';
			}
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
			// Image
			'image' => array(
				'title'       => 'Image',
				'description' => 'Optional image to showcase this page',
				'type'        => 'file',
				'basedir'     => 'public/page/image/',
				'value'       => (($meta = $this->getMeta('image')) ? $meta->get('meta_value_title') : null),
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
				'title'       => 'Description/Teaser',
				'description' => 'Teaser text that displays on search engine and social network preview links',
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

	public function getMetaValue($name){
		$m = $this->getMeta($name);

		return $m ? $m->get('meta_value_title') : '';
	}
	
	public function getAsFormArray() {
		$ret = parent::getAsFormArray();
		
		// If the user has access to manage sites globally, then enable that option!
		if(
			Core::IsComponentAvailable('multisite') && 
			MultiSiteHelper::IsEnabled() && 
			\Core\user()->checkAccess('g:admin') &&
			isset($ret['site'])
		){
			$opts = $ret['site']->getAsArray();
			unset($opts['__class']);
			
			$ret['site'] = \Core\Forms\FormElement::Factory( 'select', $opts );
			
			// Ensure that the form element's "parent" is the same as this column's parent.
			// If it's null, then it'll be null there! (which is fine.)
			// Remember since this is an object, only the REFERENCE will be used.
			$ret['site']->parent = $this;
		}
		
		// Add in some extra fields that are associated with Pages too!
		// I need to add the rewrite options, (I need to get them too).
		$ret['rewrites'] = \Core\Forms\FormElement::Factory('textarea',
			[
				'name' => 'rewrites',
				'group' => 'Meta Information & URL (SEO)',
				'title' => 'Rewrite Aliases',
				'value' => $this->getRewriteURLs(),
				'description' => 'Enter rewrite aliases that point to this page, one per line.  You may use the fully resolved path or simply the part after the ".com".',
			]
		);

		// I need to add the pagemetas!
		foreach($this->getMetasArray() as $key => $dat){
			$type = $dat['type'];
			unset($dat['type']);
			$dat['name'] = 'metas[' . $key . ']';
			$dat['group'] = 'Meta Information & URL (SEO)'; 
			
			$ret['metas_' . $key] = \Core\Forms\FormElement::Factory($type, $dat);
		}

		// And the page insertables.
		$tpl = Core\Templates\Template::Factory($this->getTemplateName());
		if($tpl){
			foreach($tpl->getInsertables() as $key => $dat){
				$type = $dat['type'];
				unset($dat['type']);
				$dat['name'] = 'insertables[' . $key . ']';
				$dat['group'] = 'Basic';
				$dat['class'] = 'insertable';

				// This insertable may already have content from the database... if so I want to pull that!
				$i = InsertableModel::Construct($this->get('site'), $this->get('baseurl'), $key);
				if ($i->get('value') !== null){
					$dat['value'] = $i->get('value');
				}

				$ret['insertables_' . $key] = \Core\Forms\FormElement::Factory($type, $dat);
			}
		}
		
		return $ret;
	}
	
	
	public function render($key){
		if($key == 'site'){
			$s = $this->get('site');
			if($s == -1){
				return 'Global';
			}
			elseif($s == 0){
				return 'Root-Only';
			}
			else{
				return 'Local (' . $s . ')';
			}
		}
		elseif($key == 'title'){
			$p = $this->getParent();
			return ($p ? $p->get('title') . ' &raquo;' : '') . $this->get('title');
		}
		elseif($key == 'expires'){
			$e = $this->get('expires');
			
			if($e == 0){
				return t('STRING_DISABLED');
			}
			else{
				return \Core\time_duration_format($e);
			}
		}
		elseif($key == 'created' || $key == 'updated'){
			return \Core\Date\DateTime::FormatString($this->get($key), 'SD');
		}
		elseif($key == 'status'){
			return $this->getPublishedStatus();
		}
		elseif($key == 'published'){
			$d = $this->get('published');
			if($d){
				return \Core\Date\DateTime::FormatString($d, 'SD');
			}
			else{
				return t('STRING_NOT_PUBLISHED');
			}
		}
		elseif($key == 'published_expires'){
			$d = $this->get('published_expires');
			if($d){
				return \Core\Date\DateTime::FormatString($d, 'SD');
			}
			else{
				return t('STRING_NO_EXPIRATION');
			}
		}
		elseif($key == 'seotitle'){
			return $this->getSEOTitle();
		}
		elseif($key == 'teaser'){
			return $this->getTeaser();
		}
		else{
			return parent::render($key);
		}
	}
	

	public function set($k, $v){
		if($k == 'site'){
			// I need to run through and ensure that insertables remain linked up to the same site ID as their parent page.
			// This is a bit atypical on page creation because when the insertable is created, the page doesn't exist yet,
			// and potentially has an invalid site ID, that is only corrected after the insertables are assigned.
			//
			// Please note, this only applies when in multi-site mode.
			$insertables = $this->getLink('Insertable');

			foreach($insertables as $ins){
				/** @var $ins InsertableModel */
				$ins->set('site', $v);
			}
		}

		parent::set($k, $v);
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
					$this->deleteLink($meta);
				}
			}

			// Any new incoming keywords left?
			foreach($value as $metavalue => $metavaluetitle){
				if(!$metavaluetitle) continue;

				$meta = new PageMetaModel($this->get('site'), $this->get('baseurl'), 'keyword', $metavalue);
				$meta->set('meta_value_title', $metavaluetitle);

				// And append it so it'll get saved on save!
				$this->setLink('PageMeta', $meta);
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
			$this->setLink('PageMeta', $meta);
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
					$this->deleteLink($meta);
				}
				return; // :)
			}

			// Doesn't exist?
			if($value !== null){
				$meta = new PageMetaModel($this->get('site'), $this->get('baseurl'), $name, '');
				$meta->set('meta_value_title', $value);

				// And append it so it'll get saved on save!
				$this->setLink('PageMeta', $meta);
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
				$ins->set('site', $this->get('site'));
				$ins->set('value', $value);
				return; // :)
			}
		}

		// Doesn't exist?
		$ins = new InsertableModel($this->get('site'), $this->get('baseurl'), $name);
		$ins->set('value', $value);

		// And append it so it'll get saved on save!
		$this->setLink('Insertable', $ins);
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
	public function setFromForm(\Core\Forms\Form $form, $prefix = null){

		// Pull the meta and insertables to try to minimize data loss when changing the site id.
		$this->getLink('Insertable');
		$this->getLink('PageMeta');

		// This will take care of all the standard elements.
		parent::setFromForm($form, $prefix);

		if($form->getElement($prefix . '[rewrites]')){
			// And this will take care of the rewrites
			$rewrites = $form->getElement($prefix . '[rewrites]')->get('value');
			$this->setRewriteURLs($rewrites);
		}

		// And this will take care of the meta elements.
		$baselen = strlen($prefix . '[metas]');
		$elements = $form->getElements(true, false);
		foreach($elements as $el){
			/** @var FormElement $el */
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

	public function setToFormElement($key, \Core\Forms\FormElement $element){
		if($key == 'page_template'){
			// Make sure to set the element's templatename.
			$element->set('templatename', $this->getBaseTemplateName());
		}
	}

	public function getResolvedURL() {
		if(strpos($this->get('baseurl'), '://') !== false){
			return $this->get('baseurl');
		}

		// If enterprise // multisite mode is enabled and this page model is NOT the current site...
		// I need to lookup THAT site's root url and use that instead.
		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
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

	public function save($defer = false) {
		// Ensure some helper variables are set.
		if (!$this->get('rewriteurl')){
			$this->set('rewriteurl', $this->get('baseurl'));
		}

		// If the rewrite URL was changed, I need to invalidate the cache.
		// This is because many components that may change the url, will immediately want to reload to that new url.
		/** @var \Core\Datamodel\Columns\SchemaColumn $c */
		$c = $this->_columns['rewriteurl'];
		if($c->changed()){
			// Append this page to the rewrite cache instead of invalidating the entire thing to save a few DB queries.
			$siteid  = ($this->get('site') == -1) ? '_GLOBAL_' : $this->get('site');
			self::$_RewriteCache[$siteid][ $this->get('rewriteurl') ] = $this->get('baseurl');
			
			// And set the authoritative rewrite URL to the new one.
			self::$_BaseCache[$siteid][ $this->get('baseurl') ] = $this->get('rewriteurl');
		}

		// If this model existed before and the URL has changed, update the lookup table!
		// This will act as a basis of rewrite rules for changed URLs, allowing users to change
		// their pages rewriteurls without adversely affecting inbounding links.
		if($this->exists() && $c->changed()){
			// I don't care if the map existed, or was linked to something else...
			// All I need to do is ensure that it will redirect to the new URL.
			$map = new RewriteMapModel($this->get('rewriteurl'));
			$map->set('site', $this->get('site'));
			$map->set('baseurl', $this->get('baseurl'));
			$map->set('fuzzy', $this->get('fuzzy'));
			$map->save();
		}

		// Make sure the page's published date is correct.
		if($this->get('published_status') == 'published' && !$this->get('published')){
			// If this is set to published, but the date hasn't been set yet, (ie: the user just didn't fill in a date),
			// set the published date to right now!
			$this->set('published', \Core\Date\DateTime::NowGMT());
		}
		elseif($this->get('published_status') == 'draft'){
			// Draft pages are not allowed to have a published date at all.
			// This doesn't have any technical reason, simply to keep the data clean.
			$this->set('published', 0);
		}

		// Update this page's popularity score, just for freshness.
		$this->set('popularity', $this->getPopularityScore());

		return parent::save($defer);
	}

	/**
	 * Get the immediate parent page of this page, based on its parenturl.
	 *
	 * Will return null if this page has no parent.
	 *
	 * @return PageModel|null
	 */
	public function getParent(){

		if(!$this->exists()){
			return null;
		}

		$tree = $this->getParentTree();
		if(!sizeof($tree)){
			return null;
		}

		$last = sizeof($tree) - 1;

		return $tree[$last];
	}

	public function getParentTree() {
		// Allow pages that do not exist to have a bit of "extended" logic for determining the breadcrumbs.
		if (!$this->exists()) {
			// Do a bit of custom logic here.
			// This is all to try to guess and populate the breadcrumbs so the developer doesn't have to.

			$m               = strtolower($this->getControllerMethod());
			$b               = strtolower($this->get('baseurl'));
			$controllerclass = $this->getControllerClass();
			$hasview         = method_exists($controllerclass, 'view');
			$hasadmin        = method_exists($controllerclass, 'admin');

			// If the page is currently Edit and there is a View for this specific object.
			// These pages get the underlying model as the breadcrumb parent.
			if (
				($m == 'edit' || $m == 'update' || $m == 'delete') && $hasview
			) {
				// Replace the current baseurl with a /view version.
				$altbaseurl = str_replace('/' . $m . '/', '/view/', $b);

				/** @var PageModel $p */
				$p = PageModel::Construct($altbaseurl);
				if ($p->exists() && \Core\user()->checkAccess($p->get('access'))) {
					// I need the array merge because getParentTree only returns << parents >>.
					return array_merge($p->getParentTree(), array($p));
				}
				elseif(!$p->exists() && Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
					// Perform the same check with a "-1" for the site ID.
					// This is because many admin pages are global.
					// Reset the Page and re-perform the check.
					$p = PageModel::Construct(-1, $altbaseurl);
					if ($p->exists() && \Core\user()->checkAccess($p->get('access'))) {
						// I need the array merge because getParentTree only returns << parents >>.
						return array_merge($p->getParentTree(), array($p));
					}
				}
			}

			// If the page is currently update, edit, or create and there is an "admin" page, link that instead.
			if(
				($m == 'create' || $m == 'update' || $m == 'edit' || $m == 'delete') && $hasadmin
			){
				// Replace the current baseurl with a /admin version.
				$parentb = strpos($b, '/' . $m) ? substr($b, 0, strpos($b, '/' . $m)) : $b;
				// Append the /admin method
				$parentb .= '/admin';
				/** @var PageModel $p */
				$p = PageModel::Construct($parentb);
				if ($p->exists() && \Core\user()->checkAccess($p->get('access'))) {
					// I need the array merge because getParentTree only returns << parents >>.
					return array_merge($p->getParentTree(), array($p));
				}
				elseif(!$p->exists() && Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
					// Perform the same check with a "-1" for the site ID.
					// This is because many admin pages are global.
					// Reset the Page and re-perform the check.
					$p = PageModel::Construct(-1, $parentb);
					if ($p->exists() && \Core\user()->checkAccess($p->get('access'))) {
						// I need the array merge because getParentTree only returns << parents >>.
						return array_merge($p->getParentTree(), array($p));
					}
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

	/**
	 * Calculate the popularity score of this page based on views and age.
	 *
	 * This will actually perform the calculation, not just use the cached version.
	 *
	 * @return float
	 */
	public function getPopularityScore(){
		$score = $this->get('pageviews');
		$created = $this->get('published');

		if(!$created){
			// If this page is not published yet, then it shouldn't have a score.
			return 0.000;
		}

		// Pages that are not indexable never have a score either.
		// This helps keep down on the admin pages being updated.
		if(!$this->get('indexable')){
			return 0.000;
		}

		if($score == 0){
			return 0.000;
		}

		// log(10)   = 1
		// log(100)  = 2
		// log(1000) = 3
		$order = log10($score);

		// Number of seconds that have elapsed since creation and now.
		$seconds = time() - $created;

		// Convert the seconds age to months.  Each month that lapses is another magnitude required to remain popular.
		// (scratch that, 57, [or two months], as a date basis is better.)
		$months = $seconds / SECONDS_TWO_MONTH;

		// If the age is less than a week, then average the age up to a week to pad new pages a little.
		// This helps prevent new pages from severely out-ranking actually-popular pages.
		$months = max($months, 0.5);

		// This effectively makes it so
		// 10 views     now         = 0.5 (with the above skewing)
		// 32 views     now         = 1.
		// 100 views    in 2 months = 1.
		// 1000 views   in 4 months = 1.
		// 10000 views  in 6 months = 1.
		// 100000 views in 8 months = 1.
		$long_number = $order - $months;

		// Increment score by 10 to help keep them above non-ranked pages.
		$long_number += 10;

		return round($long_number, 5);
	}
	
	/**
	 * Get the title of this page, (with automatic i18n translation)
	 *
	 * @return string
	 */
	public function getTitle(){
		$t = $this->get('title');
		
		if(strpos($t, 't:') === 0){
			return t(substr($t, 2));
		}
		else{
			return $t;
		}
	}

	/**
	 * Get the *automatic* SEO title for this page.
	 *
	 * This can be overridden by setting the meta title attribute explicitly!
	 *
	 * @return string
	 */
	public function getSEOTitle(){
		$metatitle = $this->getMeta('title');
		$config = \ConfigHandler::Get('/core/page/title_template');

		if($metatitle && $metatitle->get('meta_value_title')){
			// This is the meta attribute from the page.
			$t = $metatitle->get('meta_value_title');
		}
		elseif($config){
			// If there is a template set, then render with that.
			$t = $this->_parseTemplateString($config);
		}
		else{
			// Otherwise, just pull the page's title.
			$t = $this->getTitle();
		}



		if(ConfigHandler::Get('/core/page/title_remove_stop_words')){
			$stopwords = \Core\get_stop_words();

			$exploded = explode(' ', $t);
			$nt = '';
			foreach($exploded as $w){
				$lw = strtolower($w);
				if(!in_array($lw, $stopwords)){
					$nt .= ' ' . $w;
				}
			}
			$t = trim($nt);
		}

		return $t;
	}

	/**
	 * Get the teaser of this page, aka meta description
	 *
	 * @param boolean $require_something Set to true if you want to require *something* to be returned.
	 * @return string
	 */
	public function getTeaser($require_something = false){
		$meta = $this->getMeta('description');
		$config = \ConfigHandler::Get('/core/page/teaser_template');

		if($meta){
			// An explicit meta tag always overrides any template settings.
			return $meta->get('meta_value_title');
		}
		elseif($config){
			// Next up is the configuration template.
			return $this->_parseTemplateString($config);
		}
		elseif($require_something){
			// Return the body text.
			return substr(strip_tags($this->get('body')), 0, 150);
		}
		else{
			return '';
		}
	}
	/**
	 * Get the image object or null
	 *
	 * @return Core\Filestore\File|null
	 */
	public function getImage(){
		$meta = $this->getMeta('image');
		if(!$meta){
			return null;
		}

		$file = $meta->get('meta_value_title');

		if(!$file){
			return null;
		}
		$f = \Core\Filestore\Factory::File($file);
		return $f;
	}

	/**
	 * Get the image object or null
	 *
	 * @return UserModel|null
	 */
	public function getAuthor(){
		$meta = $this->getMeta('author');
		if(!$meta) return null;

		$uid = $meta->get('meta_value');

		if(!$uid) return null;

		$u = UserModel::Construct($uid);
		return $u;
	}

	/**
	 * Get the cache key for this page's index that is acceptable for use on caching systems.
	 *
	 * @return string
	 */
	public function getIndexCacheKey(){
		return 'page-cache-index-' . $this->get('site') . '-' . md5($this->get('baseurl'));
	}
	
	public function getControlLinks() {
		$admin = \Core\user()->checkAccess('g:admin');
		$access = \Core\user()->checkAccess($this->get('access'));
		$baseurl = $this->get('baseurl');
		$ret = [];
		
		if($access){
			$ret[] = [
				'title' => 't:STRING_VIEW',
				'icon' => 'view',
				'link' => $baseurl,
			];
		}
		
		if($admin){
			if($this->get('editurl')){
				$ret[] = [
					'title' => 't:STRING_EDIT',
					'icon' => 'edit',
					'link' => $this->get('editurl'),
				];
			}
			
			switch($this->getPublishedStatus()){
				case 'draft':
					$ret[] = [
						'title'   => 't:STRING_PUBLISH_PAGE',
						'icon'    => 'thumbs-up',
						'link'    => '/admin/page/publish?baseurl=' . $baseurl,
						'confirm' => '',
					];
					break;
				case 'expired':
					$ret[] = [
						'title'   => 't:STRING_REPUBLISH_PAGE',
						'icon'    => 'thumbs-up',
						'link'    => '/admin/page/publish?baseurl=' . $baseurl,
						'confirm' => '',
					];
					break;
				case 'published':
					$ret[] = [
						'title'   => 't:STRING_UNPUBLISH_PAGE',
						'icon'    => 'thumbs-down',
						'link'    => '/admin/page/unpublish?baseurl=' . $baseurl,
						'confirm' => '',
					];
					break;
			}
			
			if($this->get('deleteurl')){
				$ret[] = [
					'title'   => 't:STRING_DELETE',
					'icon'    => 'remove',
					'link'    => $this->get('deleteurl'),
					'confirm' => 't:MESSAGE_ASK_COMPLETEY_DELETE_PAGE',
				];
			}
		}
		
		$parent = parent::getControlLinks();
		
		return array_merge($ret, $parent);
	}

	/**
	 * Get a textual representation of this Model as a flat string.
	 *
	 * Used by the search systems to index the model, (or multiple models into one).
	 * 
	 * This is the PageModel specific version as Pages behave differently than most other systems.
	 *
	 * @return string
	 */
	public function getSearchIndexString(){
		// The default behaviour is to sift through the records on this model itself.
		$strs = [];
		
		// First string to pull in are the various URLs!
		$strs[] = $this->getResolvedURL();
		$strs[] = $this->getRewriteURLs();
		
		// Title is important.
		$strs[] = $this->get('title');
		// As is the SEO title
		$strs[] = $this->getSEOTitle();
		
		// Gimme some metadata.
		$strs[] = $this->getTeaser(true);
		
		// Lastly the body!
		// This is probably going to be HTML, (one would hope at least!)
		// So convert it to text before saving.
		// Otherwise there would be a million "p" entries in the string!
		$body = $this->get('body');
		$converter = new HTMLToMD\Converter();
		$strs[] = $converter->convert($body);

		return implode(' ', $strs);
	}

	/**
	 * Get a string representation of the published status of this article.
	 * This is primarily an internal and administrative function, since from the user's perspective,
	 * pages are simply available or not.
	 *
	 * @return string
	 */
	public function getPublishedStatus(){
		if($this->get('published_status') == 'draft'){
			// The page is set as "draft".  Not published.
			return 'draft';
		}

		if($this->get('published') > \Core\Date\DateTime::NowGMT()){
			// The publish date is in the future. Not published.
			return 'pending';
		}

		if($this->get('published_expires') && $this->get('published_expires') <= \Core\Date\DateTime::NowGMT()){
			// The publish expire date is set but that date has already passed.  Not published.
			return 'expired';
		}

		return 'published';
	}

	/**
	 * Get all pages on the system that are not "this page", useful for the parent select.
	 * 
	 * It's public because it's called by the Form system via the model schema defined above.
	 * 
	 * @return array
	 */
	public function _getParentsAsOptions(){
		$f = new ModelFactory('PageModel');
		if ($this->get('baseurl')){
			$f->where('baseurl != ' . $this->get('baseurl'));
		}
		$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');

		return $opts;
	}

	/**
	 * Get if this page is published and the published date is at least now or earlier.
	 *
	 * @return bool
	 */
	public function isPublished(){
		return ($this->getPublishedStatus() == 'published');
	}

	/**
	 * Purge the entire page cache for this given page.
	 */
	public function purgePageCache(){
		$indexkey = $this->getIndexCacheKey();
		$index = \Core\Cache::Get($indexkey);
		if($index && is_array($index)){
			foreach($index as $key){
				\Core\Cache::Delete($key);
			}
		}
		\Core\Cache::Delete($indexkey);
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


				$lookup = self::_LookupUrl($url);
				if($lookup['found']){
					$url = $lookup['url'];
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

	/**
	 * Parse one of the SEO template options that are configurable and return the result.
	 *
	 * @param $string_template
	 *
	 * @return string
	 */
	private function _parseTemplateString($string_template){

		$parent = $this->getParent();

		$metadescription = $this->getMeta('description');
		$bodysnippet = substr(strip_tags($this->get('body')), 0, 150);
		$author = $this->getAuthor();

		$rep = [
			//Replaced with the published date of the page
			'%%date%%' => \Core\Date\DateTime::FormatString($this->get('published'), \Core\Date\DateTime::SHORTDATE),
			//Replaced with the title of the page
			'%%title%%' => $this->getTitle(),
			//Replaced with the title of the parent page of the current page
			'%%parent_title%%' => ($parent ? $parent->getTitle() : ''),
			//The site's name
			'%%sitename%%' => SITENAME,
			//Replaced with the page excerpt (or auto-generated if it does not exist)
			'%%excerpt%%' => ($metadescription ? $metadescription->get('meta_value_title') : $bodysnippet),
			//Replaced with the current tag/tags
			'%%tag%%' => '',
			//Replaced with the current search phrase
			'%%searchphrase%%' => '',
			//Replaced with the page modified time
			'%%modified%%' => \Core\Date\DateTime::FormatString($this->get('updated'), \Core\Date\DateTime::SHORTDATE),
			//Replaced with the page author's username
			'%%name%%' => ($author ? $author->getDisplayName() : ''),
			//Replaced with the current time
			'%%currenttime%%' => \Core\Date\DateTime::Now(\Core\Date\DateTime::TIME),
			//Replaced with the current date
			'%%currentdate%%' => \Core\Date\DateTime::Now(\Core\Date\DateTime::SHORTDATE),
			//Replaced with the current day
			'%%currentday%%' => \Core\Date\DateTime::Now('d'),
			//Replaced with the current month
			'%%currentmonth%%' => \Core\Date\DateTime::Now('m'),
			//Replaced with the current year
			'%%currentyear%%' => \Core\Date\DateTime::Now('Y'),
			//Replaced with the current page number (i.e. page 2 of 4)
			'%%page%%' => '1', // @todo Support for this.
			//Replaced with the current page total
			'%%pagetotal%%' => '1', // @todo Support for this.
		];

		return str_ireplace(array_keys($rep), array_values($rep), $string_template);
	}


	/****************** Helper Static Functions **************************/

	/**
	 * Split a base url into its corresponding parts, controller method and parameters.
	 * Also supports the rewriteurl.
	 *
	 * @param string   $base The URL base to lookup, aka baseurl.
	 * @param int|null $site The Site ID to lookup, only functional in multi-site mode.
	 *
	 * @return array
	 */
	public static function SplitBaseURL($base, $site = null) {

		// Example of incoming data at this point:
		// "/"
		// ""
		// "/repo.xml.gz"
		// "/controllerfoo/methodblah/param1/param2?qstr=foo"

		if (!$base) return null;

		// Support additional arguments
		// This should be done prior to checking for aliases and what not because
		// the query string parameters should NOT effect the rewrite URL and aliases.
		$args = null;
		$argstring = '';
		if (($qpos = strpos($base, '?')) !== false) {
			$argstring = urldecode(substr($base, $qpos + 1));
			preg_match_all('/([^=&]*)={0,1}([^&]*)/', $argstring, $matches);
			$args = array();
			foreach ($matches[1] as $idx => $v) {
				if (!$v){
					// This preg_match_all creates duplicate keys when there are multiple, so a=a&b=b will produce
					// [ 'a', '', 'b', '' ]
					// for some reason.
					// This continue is a quick hack to ignore that erroneous blank key.
					continue;
				}
				$a =& $args;
				while(($paranpos = strpos($v, '[')) !== false){
					$k1 = strtolower(substr($v, 0, $paranpos));
					$v = substr($v, $paranpos+1, strpos($v, ']')-$paranpos-1);

					if(!isset($a[$k1])){
						$a[$k1] = [];
					}
					$a =& $a[$k1];
				}
				$a[strtolower($v)] = $matches[2][$idx];
			}
			$base = substr($base, 0, $qpos);
		}

		// Example of incoming data at this point:
		// "/"
		//     base: "/"
		//     args: NULL
		// ""
		//     RETURNED NULL
		// "/repo.xml.gz"
		//     base: "/repo.xml.gz"
		//     args: NULL
		// "/controllerfoo/methodblah/param1/param2?qstr=foo"
		//     base: "/controllerfoo/methodblah/param1/param2"
		//     args: [qstr] => foo


		// Content Types should not dictate the controller and method either!
		$ext = 'html';
		$posofdot = strpos($base, '.');
		if($posofdot){
			// Or, it can also be the first part up until the first '.'.
			// Also if there was a dot at this level, then the URL must be something like
			// foo.ext
			// This needs to be remapped to FooController->index with the extension of 'ext'.
			$ext  = substr($base, $posofdot+1);
			$base = substr($base, 0, $posofdot);
		}

		$ctype = \Core\Filestore\extension_to_mimetype($ext);
		// Invalid mimetype?  Default to an HTML file.
		if(!$ctype){
			$ctype = 'text/html';
		}

		// Example of incoming data at this point:
		// "/"
		//     base:  "/"
		//     args:  NULL
		//     ext:   'html'
		//     ctype: 'text/html'
		//
		// ""
		//     RETURNED NULL
		//
		// "/repo.xml.gz"
		//     base:  "/repo"
		//     args:  NULL
		//     ext:   xml.gz
		//     ctype: 'application/octet-stream'
		//
		// "/controllerfoo/methodblah/param1/param2?qstr=foo"
		//     base:  "/controllerfoo/methodblah/param1/param2"
		//     args:  [qstr] => foo
		//     ext:   'html'
		//     ctype: 'text/html'


		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			if($site === null){
				$site = MultiSiteHelper::GetCurrentSiteID();
			}
		}
		else{
			$site = null;
		}

		// This will ensure that the cache is updated and that any rewrite URL is remapped to its baseurl if possible.
		$lookup = self::_LookupUrl($base, $site);

		if($lookup['found']){
			$base = $lookup['url'];
		}
		else{
			$try = $lookup['url'];

			if($site === null){
				$tries = [];
				if(isset(self::$_FuzzyCache) && is_array(self::$_FuzzyCache)){
					foreach(self::$_FuzzyCache as $dat){
						$tries = array_merge($tries, $dat);
					}
				}
			}
			elseif(isset(self::$_FuzzyCache[$site])){
				$tries = array_merge(self::$_FuzzyCache['_GLOBAL_'], self::$_FuzzyCache[$site]);
			}
			else{
				$tries = self::$_FuzzyCache['_GLOBAL_'];
			}

			while($try != '' && $try != '/') {
				if(isset($tries[$try])) {
					// The fuzzy page must have the requested arguments, they just need to be tacked onto the end of the base.
					$base = $tries[$try] . substr($base, strlen($try));
					break;
				}
				elseif(in_array($try, $tries)) {
					$base = $tries[array_search($try, $tries)] . substr($base, strlen($try));
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

		// Logic for the Controller.
		$posofslash = strpos($base, '/');


		if ($posofslash){
			// The controller is usually the first part up until the first '/'.
			$controller = substr($base, 0, $posofslash);
			$base = substr($base, $posofslash+1);
		}
		else{
			$controller = $base;
			$base = false;
		}

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
			if(!
				(is_subclass_of($controller, 'Controller_2_1') || is_subclass_of($controller, 'Controller'))
			){
				return null;
			}
		}
		else {
			// Not even found!
			return null;
		}


		// Logic for the Method.
		//if(substr_count($base, '/') >= 1){
		if ($base) {

			$posofslash = strpos($base, '/');

			// The method can be extended.
			// This means that a method can be in the format of Sites/Edit, which should resolve to Sites_Edit.
			// This only takes effect if the method exists on the controller.
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
			$method = 'index';
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
		if (!($method == 'index' && !$params)) $baseurl .= '/' . str_replace('_', '/', $method);
		$baseurl .= ($params) ? '/' . implode('/', $params) : '';


		// Rewrite URL may be useful too!
		$rewriteurl = self::_LookupReverseUrl($baseurl, $site);


		// Keep the original mimetype extension if set.
		if($ext != 'html'){
			//$rewriteurl .= '.' . \Core\Filestore\mimetype_to_extension($ctype);
			// Some binary types of data just return a generic "application/octet-stream",
			// which is useless for figuring out which extension it original was.
			$rewriteurl .= '.' . $ext;
		}

		// Keep the arguments on the rewrite version.
		if ($args) {
			$rewriteurl .= '?' . $argstring;
			if ($params) $params = array_merge($params, $args);
			else $params = $args;
		}

		if($site === null){
			$rooturl = ROOT_URL;
		}
		else{
			$rooturl = MultiSiteModel::Construct($site)->getResolvedURL();
		}

		// Reassemble everything too!
		$fullurl = trim($rooturl, '/') . '/' . trim($rewriteurl, '/');
		/*
		if($params && sizeof($params) > 0){
			foreach($params as $k => $v){
				if(is_numeric($k)){
					$fullurl .= '/' . $v;
				}
				else{
					if(strpos($fullurl, '?') === false){
						$fullurl .= '?' . $k . '=' . $v;
					}
					else{
						$fullurl .= '&' . $k . '=' . $v;
					}
				}
			}
		}
		*/
		
		
		// Load up the aliases, just in case that's needed too!
		$aliases = [];
		if($site === null){
			foreach(self::$_RewriteCache as $set){
				$aliases = array_merge($aliases, array_keys($set, $baseurl));
			}
		}
		else{
			if(isset(self::$_RewriteCache['_GLOBAL_'])){
				$aliases = array_merge($aliases, array_keys(self::$_RewriteCache['_GLOBAL_'], $baseurl));
			}
			if(isset(self::$_RewriteCache[$site])){
				$aliases = array_merge($aliases, array_keys(self::$_RewriteCache[$site], $baseurl));
			}
		}
		
		

		// Tack on the "arguments" too, these are

		return array(
			'controller' => $controller,
			'method'     => $method,
			'parameters' => $params,
			'rooturl'    => $rooturl,
			'baseurl'    => $baseurl,
			'rewriteurl' => $rewriteurl,
			'ctype'      => $ctype,
			'extension'  => $ext,
			'fullurl'    => $fullurl,
			'rewritemap' => $aliases,
		);
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

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			$g = new Core\Datamodel\DatasetWhereClause();
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

	/**
	 * Update all pages' popularity ranking as part of a hook.
	 *
	 * @return bool
	 */
	public static function PopularityMassUpdateHook(){
		$pages = PageModel::Find();
		foreach($pages as $page){
			/** @var PageModel $page */
			$page->save();
			// Yup, all that is needed to be done is save the page.
			// The PageModel will auto-update the popularity ranking on saves.
		}
		return true;
	}
	
	/**
	 * Perform a model search on the records of this PageModel.
	 * 
	 * This has extra support for key:var tags, where key can be
	 * 
	 * * keyword
	 *
	 * @param string $query The base query to search
	 * @param array $where  Any additional where parameters to add onto the factory
	 *
	 * @return array An array of ModelResult objects.
	 */
	public static function Search($query, $where = []){
		$ret = [];

		// If this object does not support searching, simply return an empty array.
		$ref = new ReflectionClass(get_called_class());

		if(!$ref->getProperty('HasSearch')->getValue()){
			return $ret;
		}

		$fac = new ModelFactory(get_called_class());

		if(sizeof($where)){
			$fac->where($where);
		}

		if($ref->getProperty('HasDeleted')->getValue()){
			$fac->where('deleted = 0');
		}
		
		// Used in the relavency check, as tags do not get calculated into the base query.
		$relAdd = 0;
		
		// Check if this query has some of the advanced query strings.
		if(($pos = strpos($query, 'tag:')) !== false){
			$tag = preg_replace('/.*tag:([a-zA-Z0-9\-]*).*/', '$1', $query);
			// And drop this query from the original query.
			$query = preg_replace('/tag:([a-zA-Z0-9\-]*)/', '', $query);
			
			$pageMetas = PageMetaModel::FindRaw(['meta_value = ' . $tag, 'meta_key = keyword']);
			$pageURLs = [];
			foreach($pageMetas as $row){
				$pageURLs[] = $row['baseurl'];
			}
			$fac->where('baseurl IN ' . implode(',', $pageURLs));
			$relAdd += 100;
		}

		if($query){
			$fac->where(\Core\Search\Helper::GetWhereClause($query));
		}
		
		foreach($fac->get() as $m){
			/** @var Model $m */
			$sr = new \Core\Search\ModelResult($query, $m);
			
			$sr->relevancy += $relAdd;
			$sr->relevancy = min($sr->relevancy, 100);

			// This may happen since the where clause can be a little open-ended.
			if($sr->relevancy < 1) continue;
			$sr->title = $m->getLabel();
			$sr->link  = $m->get('baseurl');

			$ret[] = $sr;
		}

		// Sort the results before returning them.
		// Because otherwise, what's the point of a search algorithm?!?
		usort($ret, function($a, $b) {
			/** @var $a Core\Search\ModelResult */
			/** @var $b Core\Search\ModelResult */
			return $a->relevancy < $b->relevancy;
		});

		return $ret;
	}

	/**
	 * Lookup a url in the rewrite cache.  Useful for initial rewrite -> base conversions
	 *
	 * @param string   $url  The rewrite URL to convert to a baseurl.
	 * @param int|null $site Optionally, supply a site ID to restrict the search to.
	 *
	 * @return null|array The resolved baseurl of the given URL.
	 */
	private static function _LookupUrl($url = null, $site = null) {
		self::_LoadRoutingCaches();

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			if($site === null){
				$site = MultiSiteHelper::GetCurrentSiteID();
			}
		}
		else{
			$site = null;
		}

		if ($url === null){
			// maybe this was just called to update the local rewrite and fuzzy caches.
			return null;
		}

		// All URLs are case-insensitive.
		$url = strtolower($url);

		if($site === null){
			// No site is requested or multisite mode is not enabled.
			foreach(self::$_RewriteCache as $set){
				if(isset($set[$url])){
					return [
						'found' => true,
						'url' => $set[$url],
					];
				}
			}
		}
		else{
			if(isset(self::$_RewriteCache[$site]) && isset(self::$_RewriteCache[$site][$url])){
				return [
					'found' => true,
					'url' => self::$_RewriteCache[$site][$url],
				];
			}
			elseif(isset(self::$_RewriteCache['_GLOBAL_']) && isset(self::$_RewriteCache['_GLOBAL_'][$url])){
				return [
					'found' => true,
					'url' => self::$_RewriteCache['_GLOBAL_'][$url],
				];
			}
		}

		// Otherwise if neither checks above returned a baseurl... just return the rewrite url as provided.
		return [
			'found' => false,
			'url' => $url,
		];
	}

	/**
	 * Lookup the rewrite url for a given url.  Useful for initial base -> rewrite conversions
	 *
	 * @param string $url
	 * @param int|null $site
	 *
	 * @return string
	 */
	private static function _LookupReverseUrl($url, $site = null) {
		self::_LoadRoutingCaches();

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			if($site === null){
				$site = MultiSiteHelper::GetCurrentSiteID();
			}
		}
		else{
			$site = null;
		}

		// Sift through the rewrite cache and see if it's here.
		if($site === null){
			foreach(self::$_BaseCache as $set){
				if(isset($set[$url])){
					return $set[$url];
				}
			}
		}
		else{
			if(isset(self::$_BaseCache[$site]) && isset(self::$_BaseCache[$site][$url])){
				return self::$_BaseCache[$site][$url];
			}
			elseif(isset(self::$_BaseCache['_GLOBAL_']) && isset(self::$_BaseCache['_GLOBAL_'][$url])){
				return self::$_BaseCache['_GLOBAL_'][$url];
			}
		}

		// Else try to look it up in the fuzzy pages.
		$try = $url;

		if($site === null){
			$tries = [];
			foreach(self::$_FuzzyCache as $dat){
				$tries = array_merge($tries, $dat);
			}
		}
		else{
			$tries = [];
			if(isset(self::$_FuzzyCache['_GLOBAL_'])){
				$tries = array_merge($tries, self::$_FuzzyCache['_GLOBAL_']);
			}
			if(isset(self::$_FuzzyCache[$site])){
				$tries = array_merge($tries, self::$_FuzzyCache[$site]);
			}
		}

		while($try != '' && $try != '/') {
			if(isset($tries[$try])) {
				// The fuzzy page must have the requested arguments, they just need to be tacked onto the end of the base.
				$url = $tries[$try] . substr($url, strlen($try));
				break;
			}
			elseif(in_array($try, $tries)) {
				$url = array_search($try, $tries) . substr($url, strlen($try));
				break;
			}
			$try = substr($try, 0, strrpos($try, '/'));
		}

		// Nope, just return the URL then :/
		return $url;
	}
	
	/**
	 * Load the routing caches (rewrite, fuzzy, and base), into memory.
	 * 
	 * If already loaded, nothing happens.
	 * 
	 * @return void
	 */
	private static function _LoadRoutingCaches(){
		if (self::$_RewriteCache === null) {
			$results = \Core\Datamodel\Dataset::Init()
				->select('site, rewriteurl, baseurl, fuzzy')
				->table('page')
				->executeAndGet();

			self::$_RewriteCache = [];
			self::$_FuzzyCache   = [];
			self::$_BaseCache    = [];

			foreach ($results as $row) {

				$rewrite = strtolower($row['rewriteurl']);
				$base    = strtolower($row['baseurl']);
				$siteid  = ($row['site'] == -1) ? '_GLOBAL_' : $row['site'];

				if(!isset(self::$_RewriteCache[$siteid])){
					self::$_RewriteCache[$siteid] = [];
				}

				if(!isset(self::$_FuzzyCache[$siteid])){
					self::$_FuzzyCache[$siteid] = [];
				}

				if(!isset(self::$_BaseCache[$siteid])){
					self::$_BaseCache[$siteid] = [];
				}

				// Set the rewrite to base
				self::$_RewriteCache[$siteid][$rewrite] = $base;
				
				// If fuzzy, set that to the base too
				if ($row['fuzzy']){
					self::$_FuzzyCache[$siteid][$rewrite] = $base;
				}
				
				// and the base to authoritative rewrite
				self::$_BaseCache[$siteid][$base] = $rewrite;
			}

			
			$results = \Core\Datamodel\Dataset::Init()
				->select('site, rewriteurl, baseurl, fuzzy')
				->table('rewrite_map')
				->executeAndGet();
			
			foreach ($results as $row) {

				$rewrite = strtolower($row['rewriteurl']);
				$base    = strtolower($row['baseurl']);
				$siteid  = ($row['site'] == -1) ? '_GLOBAL_' : $row['site'];

				if(!isset(self::$_RewriteCache[$siteid])){
					self::$_RewriteCache[$siteid] = [];
				}

				if(!isset(self::$_FuzzyCache[$siteid])){
					self::$_FuzzyCache[$siteid] = [];
				}

				if(!isset(self::$_BaseCache[$siteid])){
					self::$_BaseCache[$siteid] = [];
				}

				// Set the rewrite to base
				self::$_RewriteCache[$siteid][$rewrite] = $base;

				// If fuzzy, set that to the base too
				if ($row['fuzzy']){
					self::$_FuzzyCache[$siteid][$rewrite] = $base;
				}

				// Maps are NOT authoritative and do NOT update the base table!
			}
		}
	}
}
