<?php
/**
 * File for class LivefyreWidget definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130402.0336
 */


/**
 * Class LivefyreWidget description
 */
class LivefyreWidget extends Widget_2_1{
	public function view(){
		$page = PageRequest::GetSystemRequest();
		$pageview = $page->getView();
		$pagemetas = $pageview->meta;
		$view = $this->getView();

		// The main identifier for livefyre, retrieved from within the livefyre "install" section.
		// Transposed to siteId
		$siteid = ConfigHandler::Get('/livefyre/siteid');

		if(!$siteid){
			$msg = 'Livefyre is not configured yet.';
			if(\Core\user()->checkAccess('g:admin')) $msg .= '  Please <a href="' . \Core\resolve_link('/livefyre') . '">configure it now</a>';
			return $msg;
		}

		// The "article" is the base url.  This doesn't change despite changing URLs.
		// Transposed to articleId
		$article = $page->getBaseURL();

		// The title, used in the collectionMeta.
		// Transposed to title
		$title = $pageview->title;

		// The canonical URL, used in the collectionMeta.
		$url = $pageview->canonicalurl;

		$view->assign('siteId', $siteid);
		$view->assign('articleId', $article);
		$view->assign('title', $title);
		$view->assign('url', $url);
	}

	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		// Extend this method in your class and return the path you need.
		// Optional.
		return 'assets/images/previews/templates/widgets/livefyre/livefyre-comments-140.png';
	}
}
