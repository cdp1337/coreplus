<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 12/3/12
 * Time: 8:00 PM
 * Just a simple class to encapsulate the catch 404 logic and lookup the mapped URL.
 */
abstract class RewriteMapHelper {
	public static function Catch404Hook(View $view){

		$request = PageRequest::GetSystemRequest();

		// The incoming rewrite request maybe a rewrite url in the map table.
		$incomingurl = $request->uriresolved;

		// Look for it!
		$maps = RewriteMapModel::Find(array('rewriteurl' => $incomingurl));

		// Did I get one did I get one did I get one?
		if(sizeof($maps)){
			// Grab the first one, that'll be the latest, (should multiple exist.... somehow :/ )
			$match = $maps[0]->get('baseurl');

			// Resolve that to the new rewriteurl and redirect!
			$newpage = PageModel::Construct($match);
			\core\redirect($newpage->get('rewriteurl'), 301);
		}

		// Else, no match was found... maybe it's a fuzzy page!
		// Since this page will have no longer existed, I can't just use the builtin logic :(
		$fuzzy = $incomingurl;
		do{
			$fuzzy = substr($fuzzy, 0, strrpos($fuzzy, '/'));

			$fuzzymaps = RewriteMapModel::Find(array('rewriteurl' => $fuzzy, 'fuzzy' => '1'));
			if(sizeof($fuzzymaps)){
				// Yay!
				// Don't forget to throw on the rest of the url.
				$match = $fuzzymaps[0]->get('baseurl');
				$newpage = PageModel::Construct($match);
				$url = $newpage->get('rewriteurl');
				if($newpage->get('fuzzy')){
					// Only if the new page is fuzzy too.
					$url .= substr($incomingurl, strlen($fuzzy));
				}
				\core\redirect($url, 301);
			}
		}
		while($fuzzy);

		// Sigh, guess this page didn't make the cut.
	}
}
