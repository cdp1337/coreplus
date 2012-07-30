<?php

class PageWidget extends Widget_2_1{
	/**
	 * This is a widget to display siblings on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblingsnavigation() {
		$view    = $this->getView();
		$current = PageRequest::GetSystemRequest();
		$model   = $current->getPageModel();
		if(!$model) return '';
		$baseurl = $model->get('parenturl');
		if(!$baseurl) return '';

		if($model->get('admin')){
			$pages = PageModel::Find(array('admin = 1', 'baseurl != /admin'), null, 'title');
		}
		else{
			// Give me all the siblings of that baseurl.
			$pages = PageModel::Find(array('parenturl' => $baseurl), null, 'title');
		}

		$entries = array();
		foreach($pages as $page){
			$entries[] = array('obj' => $page, 'children' => array());
		}

		$view->assign('entries', $entries);
	}

	/**
	 * This is a widget to display siblings AND the active page's children on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblingsandchildrennavigation() {
		$view    = $this->getView();
		$current = PageRequest::GetSystemRequest();
		$model   = $current->getPageModel();
		if(!$model) return '';
		$baseurl = $model->get('parenturl');
		if(!$baseurl) return '';

		if($model->get('admin')){
			$pages = PageModel::Find(array('admin = 1', 'baseurl != /admin'), null, 'title');
		}
		else{
			// Give me all the siblings of that baseurl.
			$pages = PageModel::Find(array('parenturl' => $baseurl), null, 'title');
		}

		$entries = array();
		foreach($pages as $page){
			if($page->get('baseurl') == $model->get('baseurl')){
				$subpages = PageModel::Find(array('parenturl' => $model->get('baseurl')), null, 'title');
				$subentries = array();
				foreach($subpages as $subpage){
					$subentries[] = array('obj' => $subpage, 'children' => array());
				}
				$entries[] = array('obj' => $page, 'children' => $subentries);
			}
			else{
				$entries[] = array('obj' => $page, 'children' => array());
			}
		}

		$view->assign('entries', $entries);
	}
}