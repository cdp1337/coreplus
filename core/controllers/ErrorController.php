<?php
// @todo 2012.05.11 cpowell - Can I kill this file?  It doesn't seem to be doing anything.

/**
 * Description of ErrorController
 *
 * @author powellc
 */
class ErrorController extends Controller {
	public static function Error404(View $page) {
		$page->error = View::ERROR_NOTFOUND;
		return $page;
	}
}

?>
