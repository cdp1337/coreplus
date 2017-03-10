<?php

class TagsWidget extends \Core\Widget {
	public function display(){
		$view = $this->getView();
		$request = $this->getRequest();

		$title = $request->getParameter('title') ? $request->getParameter('title') : 'Tags:';
		$page = $request->getParameter('page');

		if(!$page){
			throw new Exception('tags/display widget must have a page parameter passed in that contains the page object.');
		}
		if(!$page instanceof PageModel){
			throw new Exception('tags/display widget expects a PageModel to be passed in for the page parameter.');
		}

		$keywords = $page->getMeta('keywords');

		// No keywords, no need for a widget
		if(!sizeof($keywords)) return '';

		$view->assign('title', $title);
		$view->assign('tags', $keywords);
	}
}