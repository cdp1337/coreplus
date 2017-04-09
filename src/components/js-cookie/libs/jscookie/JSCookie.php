<?php
/**
 * @todo      Enter a meaningful file description here!
 *
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20151009.1732
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license   Released under the MIT license
 */

namespace JSCookie;

class JSCookie {
	public static function IncludeJS () {
		\Core\view()->addScript('assets/js/js-cookie/js.cookie.js', 'head');
		return true;
	}
}