<?php

class SitemapController extends Controller_2_1 {
	public function index(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();

		// Give me every registered (public) page!
		$factory = new ModelFactory('PageModel');
		$factory->where('selectable = 1');
		$factory->order('title');
		// Multisite?
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$factory->whereGroup(
				'OR',
				array(
					'site = ' . MultiSiteHelper::GetCurrentSiteID(),
					'site = -1'
				)
			);
		}
		// Run this through the streamer, just in case there are a lot of pages...
		$stream = new DatasetStream($factory->getDataset());

		//$user  = User::Factory();
		$user = \Core\user();
		$toshow = array();
		while(($record = $stream->getRecord())){
			if($user->checkAccess( $record['access'] )){
				$page = new PageModel();
				$page->_loadFromRecord($record);
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