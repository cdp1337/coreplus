<?php

class SitemapController extends Controller_2_1 {
	public function index(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		// Give me every registered (public) page!
		$pages = PageModel::Find(null, null, 'title');
		$user  = User::Factory();

		$toshow = array();
		foreach($pages as $page){
			if($user->checkAccess($page->get('access'))){
				$toshow[] = $page;
			}
		}

		// This page allows for a few content types.
		switch($req->ctype){
			case View::CTYPE_XML:
				$view->contenttype = View::CTYPE_XML;
				break;
			case View::CTYPE_HTML:
				$view->contenttype = View::CTYPE_HTML;
				break;
		}

		$view->assign('pages', $toshow);
	}
}