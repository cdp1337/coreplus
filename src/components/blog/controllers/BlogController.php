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
	 * This is the frontend blog listing page
	 */
	public function index(){
		$view     = $this->getView();
		$request  = $this->getPageRequest();
		$manager  = \Core\user()->checkAccess('p:blog_manage');


		// Get a list of all the blogs on the system.  I'll get the page object from each one and see if the current user has access
		// to each one.  Then I'll have a list of ids that the user can view.
		$ids = array();
		$blogs = BlogModel::Find(null, null, null);
		foreach($blogs as $blog){
			$page     = $blog->getLink('Page');
			$editor   = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
			$viewer   = \Core\user()->checkAccess($blog->get('access')) || $editor;

			if(!$viewer) continue;

			$ids[] = $blog->get('id');
		}

		// Is the user a manager, but no blogs exist on the system?
		if($manager && !sizeof($ids)){
			\core\redirect('/blog/admin');
		}

		$filters = new FilterForm();
		$filters->haspagination = true;
		$filters->setLimit(20);
		$filters->load($this->getPageRequest());

		$factory = new ModelFactory('BlogArticleModel');
		$factory->where('blogid IN ' . implode(',', $ids));
		$factory->order('published DESC');
		if(!$editor){
			// Limit these to published articles.
			$factory->where('status = published');
		}

		$filters->applyToFactory($factory);
		$articles = $factory->get();

		//var_dump($factory, $articles); die();

		$view->mode = View::MODE_PAGEORAJAX;
		$view->assign('articles', $articles);
		$view->assign('page', $page);
		$view->assign('filters', $filters);
		if ($editor) {
			//$view->addControl('Add Blog Article', '/blog/article/create/' . $blog->get('id'), 'add');
		}
		if ($manager) {
			$view->addControl('Edit Blog Listing Page', '/blog/editindex', 'edit');
			$view->addControl('All Articles', '/blog/admin/view', 'tasks');
		}
	}

	/**
	 * Edit the index listing page.
	 */
	public function editindex() {
		if (!$this->setAccess('p:blog_manage')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view = $this->getView();
		$page = new PageModel('/blog');
		$form = new Form();
		$form->set('callsmethod', 'BlogHelper::BlogIndexFormHandler');

		$form->addModel($page, 'page');

		$form->addElement('submit', array('value' => 'Update Page Listing'));

		$view->title = 'Update Page Listing';
		$view->assignVariable('form', $form);
	}

	/**
	 * Display all blogs in an administrative interface.
	 *
	 * Requires the p:blog_manage permission.
	 */
	public function admin() {
		// This is a manager-only function!
		if (!$this->setAccess('p:blog_manage')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view  = $this->getView();
		$blogs = BlogModel::Find(null, null, null);

		$view->title = 'Blog Administration';
		$view->assignVariable('blogs', $blogs);
		$view->addControl('Add Blog', '/blog/create', 'add');
		$view->addControl('All Articles', '/blog/admin/view', 'tasks');
	}

	/**
	 * View a specific blog in an admin-type view.  This provides more analytical data than
	 */
	public function admin_view(){
		if (!$this->setAccess('p:blog_manage')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view  = $this->getView();
		$request = $this->getPageRequest();

		if($request->getParameter(0)){
			// A specific blog was requested, limit results to that!
			$blog = new BlogModel($request->getParameter(0));
			if (!$blog->exists()) {
				return View::ERROR_NOTFOUND;
			}
			$blogid = $blog->get('id');
			$title = $blog->get('title') . ' Articles';
		}
		else{
			$blog = null;
			$blogid = null;
			$title = 'All Articles';
		}


		$filters = new FilterForm();
		$filters->setName('blog-admin-' . $blogid);
		$filters->haspagination = true;
		$filters->hassort = true;
		$filters->setSortkeys(array('created', 'title', 'status', 'published', 'updated'));
		$filters->load($request);


		$factory = new ModelFactory('BlogArticleModel');
		if($blogid){
			$factory->where('blogid = ' . $blogid);
		}
		$filters->applyToFactory($factory);
		$articles = $factory->get();


		if($blogid){
			if($blog->get('type') == 'remote') {
				$view->addControl('Import Feed', '/blog/import/' . $blog->get('id'), 'exchange');
			}
			else{
				$view->addControl('Add Blog Article', '/blog/article/create/' . $blog->get('id'), 'add');
			}
		}

		$view->title = $title;
		$view->assign('articles', $articles);
		$view->assign('filters', $filters);
		$view->assign('blogid', $blogid);

		//var_dump($articles);
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
		$manager = \Core\user()->checkAccess('p:blog_manage');
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
			if ($article->get('status') != 'published' && !$viewer) {
				return View::ERROR_NOTFOUND;
			}

			return $this->_viewBlogArticle($blog, $article);
		}
	}

	/**
	 * Create a new blog page
	 */
	public function create() {
		if (!$this->setAccess('p:blog_manage')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view = $this->getView();
		$blog = new BlogModel();
		$form = new Form();
		$form->set('callsmethod', 'BlogHelper::BlogFormHandler');

		$form->addModel($blog->getLink('Page'), 'page');
		$form->addModel($blog, 'model');

		$form->addElement('submit', array('value' => 'Create'));

		$view->addBreadcrumb('Blog Administration', '/blog/admin');
		$view->title = 'Create Blog';
		$view->assignVariable('form', $form);
	}

	/**
	 * Update an existing blog page
	 */
	public function update() {
		if (!$this->setAccess('p:blog_manage')) {
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
		$view->title = 'Update Blog';
		$view->assignVariable('form', $form);
	}

	/**
	 * View to import a given feed into the system.
	 *
	 * @return int
	 */
	public function import() {
		if (!$this->setAccess('p:blog_manage')) {
			return View::ERROR_ACCESSDENIED;
		}

		$view    = $this->getView();
		$request = $this->getPageRequest();
		$blog    = new BlogModel($request->getParameter(0));
		$blogid  = $blog->get('id');
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}

		// Try to perform the import.
		try{
			$results = $blog->importFeed();
		}
		catch(Exception $e){
			Core::SetMessage($e->getMessage(), 'error');
			\Core\go_back();
		}

		$view->addBreadcrumb($blog->get('title'), $blog->get('rewriteurl'));
		$view->title = 'Import Blog Feed';
		$view->assign('changelog', $results['changelog']);
		$view->assign('added', $results['added']);
		$view->assign('updated', $results['updated']);
		$view->assign('skipped', $results['skipped']);
		$view->assign('deleted', $results['deleted']);
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
		$manager = \Core\user()->checkAccess('p:blog_manage');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;

		if (!$manager) {
			return View::ERROR_ACCESSDENIED;
		}

		if (!$request->isPost()) {
			return View::ERROR_BADREQUEST;
		}

		$blog->delete();
		\core\redirect('/blog/admin');
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
		$manager = \Core\user()->checkAccess('p:blog_manage');
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

		$blog = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:blog_manage');
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

		$form = BlogHelper::GetArticleForm($article);

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
		$manager = \Core\user()->checkAccess('p:blog_manage');
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
		$manager = \Core\user()->checkAccess('p:blog_manage');
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

		$blog = new BlogModel($request->getParameter(0));
		if (!$blog->exists()) {
			return View::ERROR_NOTFOUND;
		}
		$manager = \Core\user()->checkAccess('p:blog_manage');
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

		if (!$request->isPost()) {
			return View::ERROR_BADREQUEST;
		}

		$article->delete();
		Core::GoBack(1);
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

		$manager = \Core\user()->checkAccess('p:blog_manage');
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

		return $this->_viewBlogArticle($blog, $article);
	}

	private function _viewBlog(BlogModel $blog) {
		$view     = $this->getView();
		$page     = $blog->getLink('Page');
		$request  = $this->getPageRequest();

		$manager  = \Core\user()->checkAccess('p:blog_manage');
		$editor   = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
		$viewer   = \Core\user()->checkAccess($blog->get('access')) || $editor;

		// Get the latest published article's update date.  This will be used for the blog updated timestamp.
		$latest = $blog->getLinkFactory('BlogArticle');
		$latest->order('published DESC');
		$latest->where('status = published');
		$latest->limit(1);
		$latestarticle = $latest->get();


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

		$factory = $blog->getLinkFactory('BlogArticle');
		$factory->order('published DESC');
		if(!$editor){
			// Limit these to published articles.
			$factory->where('status = published');
		}

		$filters->applyToFactory($factory);
		$articles = $factory->get();

		$view->mode = View::MODE_PAGEORAJAX;
		$view->assign('blog', $blog);
		$view->assign('articles', $articles);
		$view->assign('page', $page);
		$view->assign('filters', $filters);
		$view->assign('canonical_url', Core::ResolveLink($blog->get('baseurl')));
		$view->assign('last_updated', ($latestarticle ? $latestarticle->get('updated') : 0));
		$view->assign('servername', SERVERNAME_NOSSL);

		// Add the extra view types for this page
		$view->AddHead('<link rel="alternate" type="application/atom+xml" title="' . $page->get('title') . ' Atom Feed" href="' . Core::ResolveLink($blog->get('baseurl')) . '.atom"/>');
		$view->AddHead('<link rel="alternate" type="application/rss+xml" title="' . $page->get('title') . ' RSS Feed" href="' . Core::ResolveLink($blog->get('baseurl')) . '.rss"/>');

		if ($editor){
			if($blog->get('type') == 'remote') {
				$view->addControl('Import Feed', '/blog/import/' . $blog->get('id'), 'exchange');
			}
			else{
				$view->addControl('Add Blog Article', '/blog/article/create/' . $blog->get('id'), 'add');
			}
		}
		if ($manager) {
			$view->addControl('Edit Blog', '/blog/update/' . $blog->get('id'), 'edit');
			$view->addControl('All Articles', '/blog/admin/view/' . $blog->get('id'), 'tasks');
		}
		$view->addControl('RSS Feed', Core::ResolveLink($blog->get('baseurl')) . '.rss', 'rss');
		//$view->addControl('Atom Feed', Core::ResolveLink($blog->get('baseurl')) . '.atom', 'rss');
	}

	private function _viewBlogArticle(BlogModel $blog, BlogArticleModel $article) {
		$view = $this->getView();
		/** @var $page PageModel */
		$page = $article->getLink('Page');
		//$articles = $blog->getLink('BlogArticle');
		$manager = \Core\user()->checkAccess('p:blog_manage');
		$editor  = \Core\user()->checkAccess($blog->get('manage_articles_permission ')) || $manager;
		$author = User::Find(array('id' => $article->get('authorid')));

		//var_dump($page->getMeta('keywords')); die();


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
		if($author){
			/** @var $author User */
			//$view->meta['author'] = $author->getDisplayName();
			$view->meta['author'] = $author;
		}
		$view->meta['description'] = $article->getTeaser();
		$view->assign('author', $author);
		$view->assign('article', $article);
		$view->assign('body', \Core\parse_html($article->get('body')));

		if ($editor) {
			$view->addControl('Edit Article', '/blog/article/update/' . $blog->get('id') . '/' . $article->get('id'), 'edit');
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
					'link'    => '/blog/article/delete/' . $blog->get('id') . '/' . $article->get('id'),
					'icon'    => 'remove',
					'confirm' => 'Remove blog article?'
				)
			);
		}
	}
}
