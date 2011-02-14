<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorController
 *
 * @author powellc
 */
class ErrorController extends Controller{
	public static function Error404(View $page) {
		$page->error = View::ERROR_NOTFOUND;
		return $page;
	}
}

?>
