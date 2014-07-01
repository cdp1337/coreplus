<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/29/12
 * Time: 9:52 PM
 * To change this template use File | Settings | File Templates.
 */
class BlogController extends Controller_2_1 {

	/**
	 * The frontend listing page that displays all blog articles that are published across the system.
	 */
	public function index(){
		$view     = $this->getView();
		$request  = $this->getPageRequest();
		$manager  = \Core\user()->checkAccess('p:/blog/manage_all');


		// Get a list of all the blogs on the system.  I'll get the page object from each one and see if the current user has access
		// to each one.  Then I'll have a list of ids that the user can view.
		$parents = array();
		$editor = false;
		$page = null;
		$blogs = BlogModel::Find(null, null, null);
		foreach($blogs as $blog){
			/** @var BlogModel $blog */
			$page     = $blog->getLink('Page');
			$editor   = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
			$viewer   = \Core\user()->checkAccess($blog->get('access')) || $editor;

			if(!$viewer) continue;

			$parents[] = $blog->get('baseurl');
		}

		// Is the user a manager, but no blogs exist on the system?
		if($manager && !sizeof($parents)){
			Core::SetMessage('There are no blogs on the system currently, you can use the All Pages interface to create one.', 'tutorial');
			\core\redirect('/admin/pages');
		}

		$filters = new FilterForm();
		$filters->haspagination = true;
		$filters->setLimit(20);
		$filters->load($this->getPageRequest());

		$factory = new ModelFactory('PageModel');

		if(sizeof($parents)){
			$factory->where('parenturl IN ' . implode(',', $parents));
		}
		else{
			// This is to prevent the system from trying to load all pages that have a parent of "".
			$factory->where('parenturl = -there-are-no-blogs-');
		}

		if($request->getParameter('q')){
			$query = $request->getParameter('q');
			$factory->where(\Core\Search\Helper::GetWhereClause($request->getParameter('q')));
		}
		else{
			$query = null;
		}

		$factory->order('published DESC');
		if(!$editor){
			// Limit these to published articles.
			$factory->where('published_status = published');
			// And where the published date is >= now.
			$factory->where('published <= ' . CoreDateTime::Now('U', Time::TIMEZONE_GMT));
		}

		$filters->applyToFactory($factory);
		$articles = $factory->get();

		//var_dump($factory, $articles); die();

		$view->mode = View::MODE_PAGEORAJAX;
		$view->assign('articles', $articles);
		$view->assign('page', $page);
		$view->assign('filters', $filters);
		$view->assign('query', $query);
		if ($editor) {
			//$view->addControl('Add Blog Article', '/blog/article/create/' . $blog->get('id'), 'add');
		}
		if ($manager) {
			$view->addControl('Edit Blog Listing Page', '/blog/editindex', 'edit');
			$view->addControl('Create New Blog', '/blog/create', 'add');
			$view->addControl('All Articles', '/admin/pages/?filter[parenturl]=/blog/view/', 'tasks');
		}
	}

	/**
	 * Edit the index listing page.
	 */
	public function editindex() {
		if (!$this->setAccess('p:/blog/manage_all')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view = $this->getView();
		$page = new PageModel('/blog');
		$form = new Form();
		$form->set('callsmethod', 'BlogHelper::BlogIndexFormHandler');

		$form->addModel($page, 'page');

		$form->addElement('submit', array('value' => 'Update Page Listing'));

		$view->mastertemplate = 'admin';
		$view->title = 'Blog Listing';
		$view->assignVariable('form', $form);
	}

	/**
	 * This is the main function responsible for displaying nearly all public content.
	 *
	 * This is because the entries will be sub URLs of this one, thus preserving URL structures.
	 */
	public function view() {
		$request = $this->getPageRequest();

		$blog = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
		$viewer  = \Core\user()->checkAccess($blog->get('access')) || $editor;

		if (!$viewer) {
			return View::ERROR_ACCESSDENIED;
		}

		// Only 1 parameter; the blog page itself was requested.
		if ($request->getParameter(1) === null) {
			return $this->_viewBlog($blog);
		} // Or the user requested an article!
		else {
			$articleid = $request->getParameter(1);
			// Trim everything after the first dash.
			if (strpos($articleid, '-') !== false) $articleid = substr($articleid, 0, strpos($articleid, '-'));
			$article = new BlogArticleModel($articleid);
			if ($article->get('blogid') != $blog->get('id')) {
				return View::ERROR_NOTFOUND;
			}
			// If the article is still in the draft stage and the user does not have view permissions, (public),
			// then it's the same as a 404.
			if ($article->get('status') != 'published' && !$editor) {
				return View::ERROR_NOTFOUND;
			}

			// And where the published date is >= now.
			if($article->get('published') > CoreDateTime::Now('U', Time::TIMEZONE_GMT) && !$editor ){
				return View::ERROR_NOTFOUND;
			}

			return $this->_viewBlogArticle($blog, $article);

		}
	}

	/**
	 * Create a new blog page
	 */
	public function create() {
		if (!$this->setAccess('p:/blog/manage_all')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view = $this->getView();
		$blog = new BlogModel();
		$form = new Form();
		$form->set('callsmethod', 'BlogHelper::BlogFormHandler');

		$form->addModel($blog->getLink('Page'), 'page');
		$form->addModel($blog, 'model');

		$form->addElement('submit', array('value' => 'Create'));

		//$view->addBreadcrumb('Blog Administration', '/blog/admin');
		$view->mastertemplate = 'admin';
		$view->title = 'Create Blog';
		$view->assignVariable('form', $form);
	}

	/**
	 * Update an existing blog page
	 */
	public function update() {
		if (!$this->setAccess('p:/blog/manage_all')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view    = $this->getView();
		$request = $this->getPageRequest();
		$blog    = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}

		$form = new Form();
		$form->set('callsmethod', 'BlogHelper::BlogFormHandler');

		$form->addModel($blog->getLink('Page'), 'page');
		$form->addModel($blog, 'model');

		$form->addElement('submit', array('value' => 'Update'));

		// Some elements of the form need to be readonly.
		$form->getElement('model[type]')->set('disabled', true);

		$view->addBreadcrumb($blog->get('title'), $blog->get('rewriteurl'));
		$view->mastertemplate = 'admin';
		$view->title = 'Update Blog';
		$view->assignVariable('form', $form);
	}

	/**
	 * View to import a given feed into the system.
	 *
	 * @return int
	 */
	public function import() {
		if (!$this->setAccess('p:/blog/manage_all')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view    = $this->getView();
		$request = $this->getPageRequest();
		$blog    = new BlogModel($request->getParameter(0));
		$blogid  = $blog->get('id');
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}


		if($request->isPost()){
			$view->mode = View::MODE_NOOUTPUT;
			$view->contenttype = View::CTYPE_HTML;
			$view->record = false;
			$view->templatename = null;
			$view->render();

			// Try to perform the import.
			try{
				$results = $blog->importFeed(true);
			}
			catch(Exception $e){
				echo '<p class="message-error">' . $e->getMessage() . '</p>';
				\Core\ErrorManagement\exception_handler($e);
				die();
			}

			echo 'DONE!' . "<br/>\n";
			echo 'Added: ' . $results['added'] . "<br/>\n";
			echo 'Updated: ' . $results['updated'] . "<br/>\n";
			echo 'Skipped: ' . $results['skipped'] . "<br/>\n";
		}

		$view->addBreadcrumb($blog->get('title'), $blog->get('rewriteurl'));
		$view->title = 'Import Blog Feed';
		//$view->assign('changelog', $results['changelog']);
		//$view->assign('added', $results['added']);
		//$view->assign('updated', $results['updated']);
		//$view->assign('skipped', $results['skipped']);
		//$view->assign('deleted', $results['deleted']);
	}

	/**
	 * Delete a blog
	 */
	public function delete() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		$blog = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;

		if (!$manager) {
			return View::ERROR_ACCESSDENIED;
		}

		if (!$request->isPost()) {
			return View::ERROR_BADREQUEST;
		}

		$blog->delete();
		\core\go_back();
	}

	/**
	 * Create a new blog article
	 */
	public function article_create() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		$blog = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;

		if (!$editor) {
			return View::ERROR_ACCESSDENIED;
		}

		if($blog->get('type') == 'remote'){
			Core::SetMessage('You cannot add articles to a remote feed!', 'error');
			\Core\go_back();
		}

		$article = new BlogArticleModel();
		$article->set('blogid', $blog->get('id'));
		$form = BlogHelper::GetArticleForm($article);

		$view->mastertemplate = 'admin';
		$view->templatename = 'pages/blog/article_create_update.tpl';
		$view->addBreadcrumb($blog->get('title'), $blog->get('rewriteurl'));
		$view->title = 'Create Blog Article';
		$view->assignVariable('form', $form);
		$view->assign('article', $article);
	}

	/**
	 * Update an existing blog article
	 */
	public function article_update() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		$article = new BlogArticleModel($request->getParameter(0));
		if (!$article->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$blog = $article->getLink('Blog');
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;

		if (!$editor) {
			return View::ERROR_ACCESSDENIED;
		}

		$form = BlogHelper::GetArticleForm($article);

		$view->mastertemplate = 'admin';
		$view->templatename = 'pages/blog/article_create_update.tpl';
		$view->addBreadcrumb($blog->get('title'), $blog->get('rewriteurl'));
		$view->addBreadcrumb($article->get('title'), $article->get('rewriteurl'));
		$view->title = 'Update Blog Article';
		$view->assign('form', $form);
		$view->assign('article', $article);
	}

	/**
	 * Shortcut for publishing an article.
	 */
	public function article_publish() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		$blog = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;

		if (!$editor) {
			return View::ERROR_ACCESSDENIED;
		}

		$article = new BlogArticleModel($request->getParameter(1));
		if (!$article->exists()) {
			return View::ERROR_NOTFOUND;
		}
		if ($article->get('blogid') != $blog->get('id')) {
			return View::ERROR_NOTFOUND;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		// Is this article already published?
		if($article->get('status') == 'published'){
			Core::SetMessage('Article is already published!', 'error');
			\Core\go_back();
		}

		$article->set('status', 'published');
		$article->save();

		Core::SetMessage('Published article successfully!', 'success');
		\Core\go_back();
	}

	/**
	 * Shortcut for unpublishing an article.
	 */
	public function article_unpublish() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		$blog = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;

		if (!$editor) {
			return View::ERROR_ACCESSDENIED;
		}

		$article = new BlogArticleModel($request->getParameter(1));
		if (!$article->exists()) {
			return View::ERROR_NOTFOUND;
		}
		if ($article->get('blogid') != $blog->get('id')) {
			return View::ERROR_NOTFOUND;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		// Is this article already published?
		if($article->get('status') == 'draft'){
			Core::SetMessage('Article is already in draft mode!', 'error');
			\Core\go_back();
		}

		$article->set('status', 'draft');
		$article->save();

		Core::SetMessage('Unpublished article successfully!', 'success');
		\Core\go_back();
	}

	/**
	 * Delete a blog article
	 */
	public function article_delete() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		$article = new BlogArticleModel($request->getParameter(0));
		if (!$article->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$blog = $article->getLink('Blog');
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;

		if (!$editor) {
			return View::ERROR_ACCESSDENIED;
		}

		if (!$request->isPost()) {
			return View::ERROR_BADREQUEST;
		}

		$article->delete();
		\Core\go_back();
	}

	/**
	 * New articles that have their own rewrite url will call this method directly.
	 */
	public function article_view() {
		$request = $this->getPageRequest();

		$articleid = $request->getParameter(0);
		$article = new BlogArticleModel($articleid);
		$blog = $article->getLink('Blog');

		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}

		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
		$viewer  = \Core\user()->checkAccess($blog->get('access')) || $editor;

		if (!$viewer) {
			return View::ERROR_ACCESSDENIED;
		}

		// If the article is still in the draft stage and the user does not have edit permissions,
		// then it's the same as a 404.
		if ($article->get('status') != 'published' && !$editor) {
			return View::ERROR_NOTFOUND;
		}

		if($article->get('published') > CoreDateTime::Now('U', Time::TIMEZONE_GMT) && !$editor){
			return View::ERROR_NOTFOUND;
		}

		return $this->_viewBlogArticle($blog, $article);
	}

	/**
	 * View controller for a blog article listing page.
	 * This will only display articles under this same blog.
	 *
	 * @param BlogModel $blog
	 */
	private function _viewBlog(BlogModel $blog) {
		$view     = $this->getView();
		$page     = $blog->getLink('Page');
		$request  = $this->getPageRequest();

		$manager  = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor   = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
		$viewer   = \Core\user()->checkAccess($blog->get('access')) || $editor;

		// Get the latest published article's update date.  This will be used for the blog updated timestamp.
		// (This doesn't have a whole lot of benefit above the ModelFactory, simply illustrating a different way to query data).
		$latest = Dataset::Init()
			->select('*')
			->table('page')
			->where('parenturl = ' . $blog->get('baseurl'))
			->where('published_status = published')
			->order('published DESC')
			->limit(1)
			->current();


		$filters = new FilterForm();
		$filters->haspagination = true;

		// Allow different type of requests to come in here.
		switch($request->ctype){
			case 'application/atom+xml':
				$view->templatename = 'pages/blog/view-blog.atom.tpl';
				$view->contenttype = $request->ctype;
				$view->mastertemplate = false;
				$filters->setLimit(200);
				break;

			case 'application/rss+xml':
				$view->templatename = 'pages/blog/view-blog.rss.tpl';
				$view->contenttype = $request->ctype;
				$view->mastertemplate = false;
				$filters->setLimit(200);
				break;

			default:
				$view->templatename = 'pages/blog/view-blog.tpl';
				$filters->setLimit(20);
				break;
		}

		$filters->load($this->getPageRequest());

		$factory = new ModelFactory('PageModel');

		if($request->getParameter('q')){
			$query = $request->getParameter('q');
			$factory->where(\Core\Search\Helper::GetWhereClause($request->getParameter('q')));
		}
		else{
			$query = null;
		}

		$factory->where('parenturl = ' . $blog->get('baseurl'));
		$factory->order('published DESC');
		if(!$editor){
			// Limit these to published articles.
			$factory->where('published_status = published');

			// And where the published date is >= now.
			$factory->where('published <= ' . CoreDateTime::Now('U', Time::TIMEZONE_GMT));
		}

		$filters->applyToFactory($factory);
		$articles = $factory->get();

		$view->mode = View::MODE_PAGEORAJAX;
		$view->assign('blog', $blog);
		$view->assign('articles', $articles);
		$view->assign('page', $page);
		$view->assign('filters', $filters);
		$view->assign('canonical_url', Core::ResolveLink($blog->get('baseurl')));
		$view->assign('last_updated', ($latest ? $latest['updated'] : 0));
		$view->assign('servername', SERVERNAME_NOSSL);
		$view->assign('editor', $editor);
		$view->assign('add_article_link', '/content/create?page_template=blog-article.tpl&parenturl=' . $blog->get('baseurl'));

		// Add the extra view types for this page
		$view->addHead('<link rel="alternate" type="application/atom+xml" title="' . $page->get('title') . ' Atom Feed" href="' . Core::ResolveLink($blog->get('baseurl')) . '.atom"/>');
		$view->addHead('<link rel="alternate" type="application/rss+xml" title="' . $page->get('title') . ' RSS Feed" href="' . Core::ResolveLink($blog->get('baseurl')) . '.rss"/>');

		if ($editor){
			if($blog->get('type') == 'remote') {
				$view->addControl('Import Feed', '/blog/import/' . $blog->get('id'), 'exchange');
			}
			else{
				$view->addControl('Add Article', '/content/create?page_template=blog-article.tpl&parenturl=' . $blog->get('baseurl'), 'add');
			}
		}
		if ($manager) {
			$view->addControl('Edit Blog', '/blog/update/' . $blog->get('id'), 'edit');
			$view->addControl('All Articles', '/admin/pages/?filter[parenturl]=' . $blog->get('baseurl'), 'tasks');
		}
		$view->addControl('RSS Feed', Core::ResolveLink($blog->get('baseurl')) . '.rss', 'rss');
		//$view->addControl('Atom Feed', Core::ResolveLink($blog->get('baseurl')) . '.atom', 'rss');
	}

	private function _viewBlogArticle(BlogModel $blog, BlogArticleModel $article) {
		$view = $this->getView();
		/** @var $page PageModel */
		$page = $article->getLink('Page');
		//$articles = $blog->getLink('BlogArticle');
		$manager = \Core\user()->checkAccess('p:/blog/manage_all');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
		$author = UserModel::Construct($article->get('authorid'));

		//$authorid = $author->get('id');
		//var_dump($page->getMeta('keywords')); die();

		if(!$article->isPublished()){
			// Is it actually not published, or just marked for a future publish date?
			if($article->get('status') == 'published'){
				$publishdate = new CoreDateTime($article->get('published'));
				Core::SetMessage('Article is set to be published on ' . $publishdate->getFormatted('F jS, Y \a\t h:ia'), 'info');
			}
			else{
				Core::SetMessage('Article not published yet!', 'info');
			}
		}


		//$view->templatename = $page->get('page_template') ? $page->get('page_template') : 'pages/blog/article_view.tpl';
		$view->templatename = 'pages/blog/article_view.tpl';
		//$view->addBreadcrumb($blog->get('title'), $blog->get('rewriteurl'));
		$view->title         = $article->get('title');
		$view->meta['title'] = $article->get('title');
		$view->updated       = $article->get('updated');
		$view->canonicalurl  = Core::ResolveLink($article->get('rewriteurl'));
		$view->meta['og:type'] = 'article';
		if ($article->get('image')) {
			$image                  = \Core\Filestore\Factory::File($article->get('image'));
			$view->meta['og:image'] = $image->getPreviewURL('200x200');
		}
		//if($author){
		//	/** @var $author User */
		//	//$view->meta['author'] = $author->getDisplayName();
		//	$view->meta['author'] = $author;
		//}
		$view->meta['description'] = $article->getTeaser();
		$view->assign('author', $author->exists() ? $author : null);
		$view->assign('article', $article);
		$view->assign('body', \Core\parse_html($article->get('body')));

		if ($editor) {
			$view->addControl('Edit Article', '/blog/article/update/' . $article->get('id'), 'edit');
			if($article->get('status') == 'draft'){
				$view->addControl( [
						'title'   => 'Publish Article',
						'link'    => '/blog/article/publish/' . $blog->get('id') . '/' . $article->get('id'),
						'icon'    => 'arrow-up',
						'confirm' => 'Publish article?'
					] );
			}
			$view->addControl(
				array(
					'title'   => 'Delete Article',
					'link'    => '/blog/article/delete/' . $article->get('id'),
					'icon'    => 'remove',
					'confirm' => 'Remove blog article?'
				)
			);
		}

		// Add the extra view types for this page
		$view->addHead('<link rel="alternate" type="application/atom+xml" title="' . $page->get('title') . ' Atom Feed" href="' . Core::ResolveLink($blog->get('baseurl')) . '.atom"/>');
		$view->addHead('<link rel="alternate" type="application/rss+xml" title="' . $page->get('title') . ' RSS Feed" href="' . Core::ResolveLink($blog->get('baseurl')) . '.rss"/>');
		$view->addControl('RSS Feed', Core::ResolveLink($blog->get('baseurl')) . '.rss', 'rss');
	}
}
