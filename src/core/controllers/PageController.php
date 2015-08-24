<?php
/**
 * File for class PageController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140314.1632
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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


/**
 * A short teaser of what PageController does.
 *
 * More lengthy description of what PageController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for PageController
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class PageController extends Controller_2_1 {
	public function search(){
		$view     = $this->getView();
		$request  = $this->getPageRequest();

		if(!$request->getParameter('q')){
			// Simply redirect to the sitemap if no query was provided.
			\Core\redirect('/page/sitemap');
		}

		$search = new \Core\Search\SearchResults();
		$search->query = $request->getParameter('q');
		$results = PageModel::Search($request->getParameter('q'), ['indexable = 1']);

		$isadmin = \Core\user()->checkAccess('g:admin');

		foreach($results as $r){
			/** @var Core\Search\ModelResult $r */

			/** @var PageModel $model */
			$model = $r->_model;

			if(!$model->isPublished() && !$isadmin){
				// The page is not published and the user is not an admin, skip!
				continue;
			}

			// Skip the sitemap page iteself, as that will probably contain most of the keywords.
			if($model->get('baseurl') == '/page/sitemap'){
				continue;
			}

			if(!\Core\user()->checkAccess($model->get('access'))){
				// User does not have access to this page!
				continue;
			}

			if($r->relevancy < 10){
				// Not a good enough of a match.
				continue;
			}

			// Otherwise.
			$search->addResult($r);
		}

		$search->sortResults();

		HookHandler::DispatchHook('/core/page/search/results', $search);

		$view->title = 'Site Search';
		$view->assign('query', $request->getParameter('q'));
		$view->assign('results', $search);
	}

	public function sitemap(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();

		// Give me every registered (public) page!
		$factory = new ModelFactory('PageModel');
		$factory->where('indexable = 1');
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
			$site = MultiSiteHelper::GetCurrentSiteID();
		}
		else{
			$site = null;
		}

		// Run this through the streamer, just in case there are a lot of pages...
		$stream = new DatasetStream($factory->getDataset());

		$user = \Core\user();
		$toshow = array();
		while(($record = $stream->getRecord())){
			if(!$user->checkAccess( $record['access'] )){
				// Skip any further operations if the user does not have access to this page
				continue;
			}

			if($record['published_status'] != 'published'){
				// Skip any further operations if the page isn't even marked as published.
				continue;
			}

			$page = new PageModel();
			$page->_loadFromRecord($record);

			if(!$page->isPublished()){
				// Skip out if the page is not marked as published.
				// This has extended checks other than simply if the status is set as "published",
				// such as publish date and expiration date.
				continue;
			}

			$toshow[] = $page;
		}

		// Anything else?
		$extra = HookHandler::DispatchHook('/sitemap/getlisting');
		$toshow = array_merge($toshow, $extra);

		// This page allows for a few content types.
		switch($req->ctype){
			case View::CTYPE_XML:
				$view->contenttype = View::CTYPE_XML;
				break;
			case View::CTYPE_HTML:
				$view->contenttype = View::CTYPE_HTML;
				break;
		}

		$view->title = 'Sitemap';
		$view->assign('pages', $toshow);
		$view->assign('site', $site);
	}
} 