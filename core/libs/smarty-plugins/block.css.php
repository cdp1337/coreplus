<?php
/**
 * Smarty {css} block
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @since 2011.11
 */

/**
 * @param type $params
 * @param type $template 
 */
function smarty_block_css($params, $innercontent, $template, &$repeat){
	// This only needs to be called once.
	if($repeat) return;
	
	// media type is the first parameter to check for.
	$media = (isset($params['media'])) ? $params['media'] : 'all';
	
	// See if there's a "href" set.  If so, that's probably an asset.
	if(isset($params['href'])){
		CurrentPage::AddStylesheet($params['href'], $media);
	}
	// Styles defined inline, fine as well.  The styles will be displayed in the head.
	elseif($innercontent){
		CurrentPage::AddStyle($innercontent);
	}
}
