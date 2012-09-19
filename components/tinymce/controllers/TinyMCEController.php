<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 9/12/12
 * Time: 1:39 PM
 * To change this template use File | Settings | File Templates.
 */
class TinyMCEController extends Controller_2_1 {
	/**
	 * Get the rendered HTML template for the advlink plugin.
	 *
	 * This needs to be a full controller because it requires some of core+'s functionality to determine pages.
	 */
	public function link(){
		$view = $this->getView();

		// Since this will deal with mainly frontend data, it's doubtful that the admin would want to list admin pages.
		$pages = PageModel::GetPagesAsOptions('admin = 0');
		// For each page, resolve the url to a full url for this site.  Useful because I cannot guarantee correct
		// resolution after it goes through tinyMCE's logic.
		$pagesresolved = array();
		foreach($pages as $url => $title){
			$pagesresolved[Core::ResolveLink($url)] = $title;
		}

		$tplname = Template::ResolveFile('pages/tinymce/link.phtml');

		$view->overrideTemplate(new TemplatePHTML());

		$view->mastertemplate = false;
		$view->templatename = $tplname;
		$view->assign('pages', $pagesresolved);
	}
}
