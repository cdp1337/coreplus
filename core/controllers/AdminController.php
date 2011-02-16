<?php

class AdminController extends Controller {
	public static function Menu(View $page){
		$pages = PageModel::Find(array('admin' => '1'));
		$page->assignVariable('pages', $pages);
	}
	
	//public static function Edit()
}
