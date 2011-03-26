<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContentController
 *
 * @author powellc
 */
class NavigationController extends Controller {
	public static function Index(View $page){
		
		// @todo Check page permissions
		
		$f = NavigationModel::Find(null, null, 'title');
		
		$page->title = 'Navigation Listings';
		$page->assignVariable('navs', $f);
		
		$page->addControl('New Navigation Menu', '/Navigation/Create', 'add');
	}
	
    public static function View(View $page){
		$m = new NavigationModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;

		// @todo Check page permissions

		$page->title = $m->get('title');
		$page->metas['description'] = $m->get('description');
		$page->metas['keywords'] = $m->get('keywords');
		$page->access = $m->get('access');
		$page->assignVariable('model', $m);
		
		$page->addControl('New Navigation Menu', '/Navigation/Create', 'add');
		$page->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		$page->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$page->addControl('All Content Pages', '/Content', 'directory');
	}

	public static function Edit(View $page){
		$m = new NavigationModel($page->getParameter(0));
		
		if(!$m->exists()) return View::ERROR_NOTFOUND;

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'NavigationController::_SaveHandler');
		
		// I only want non-fuzzy pages to display.
		$pages = PageModel::GetPagesAsOptions("fuzzy = '0'");

		// @todo Check page permissions
		
		$page->title = 'Edit ' . $m->get('name');
		$page->assignVariable('model', $m);
		$page->assignVariable('form', $form);
		
		// Get the entries for this model as well.
		var_dump($m->getLink('NavigationEntry'));
		
		$page->addControl('New Navigation Menu', '/Navigation/Create', 'add');
		$page->addControl('View Page', '/Content/View/' . $m->get('id'), 'view');
		$page->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$page->addControl('Navigation Listings', '/Navigation', 'directory');
	}

	public static function Create(View $page){
		$m = new NavigationModel();

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'NavigationController::_SaveHandler');
		
		// I only want non-fuzzy pages to display.
		$pages = PageModel::GetPagesAsOptions("fuzzy = '0'");

		// @todo Check page permissions

		$page->title = 'New Navigation Menu';
		$page->assignVariable('model', $m);
		$page->assignVariable('form', $form);
		$page->assignVariable('pages', $pages);
		$page->addControl('Navigation Listings', '/Navigation', 'directory');
	}
	
	public static function _SaveHandler(Form $form){
		// @todo Check page permissions

		// Save the model
		$m = $form->getModel();
		$m->save();
		
		// Save the widget too
		$widget = $m->getLink('Widget');
		
		
		echo '<pre>';var_dump($_POST, $m);
		// "entry[new1]=root&entry[new2]=root"
		// This will split up all the entries based on their sort order and parent.
		preg_match_all('/entry\[([^\]]*)\]=([^&]*)/', $_POST['entries-sorting'], $matches);
		// $matches[1] - array of the IDs of the entries
		// $matches[2] - array of the parents of the entries, same key as $matches[1].
		
		foreach($matches[1] as $k => $id){
			if(strpos($id, 'new') !== false) $entry = new NavigationEntryModel();
			else $entry = new NavigationEntryModel($id);
			
			// Set the weight, based on the key...
			$entry->set('weight', $k);
			
			// Make sure it links up to the right navigation...
			$entry->set('navigationid', $m->get('id'));
			
			// Set the correct parent...
			$entry->set('parentid', ((($matches[2][$k]) == 'root')? 0 : $matches[2][$k]) );
			
			// And the data from the regular form...
			$entry->set('type',    $_POST['entries'][$id]['type']);
			$entry->set('baseurl', $_POST['entries'][$id]['url']);
			$entry->set('title',   $_POST['entries'][$id]['title']);
			$entry->set('target',  $_POST['entries'][$id]['target']);
			var_dump($_POST['entries'][$id], $entry);
			$entry->save();
			
			// I need to update the link of any other element with this as the parent.
			if(strpos($id, 'new') !== false){
				foreach($matches[2] as $sk => $sv){
					if($sv == $id) $matches[2][$sk] = $entry->get('id');
				}
			}
		}
		
		var_dump($matches);
		die();
		
		// These pages are widget-able.
		$m->getLink('Page')->set('widget', true);
		
		return $m->get('baseurl');
	}
	
	public static function Delete(View $page){
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;
		
		if($page->getParameter(1) == 'confirm'){
			$m->delete();
			Core::Redirect('/Content');
		}
		
		$page->title = 'Confirm Delete ' . $m->get('title');
		$page->assignVariable('model', $m);
		
		$page->addControl('New Navigation Menu', '/Navigation/Create', 'add');
		$page->addControl('View Page', '/Content/View/' . $m->get('id'), 'view');
		$page->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		$page->addControl('All Content Pages', '/Content', 'directory');
	}
}
?>
