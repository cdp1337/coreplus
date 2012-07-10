<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/10/12
 * Time: 1:37 AM
 * To change this template use File | Settings | File Templates.
 */
class FontAwesome {
	public static function Load(){
		CurrentPage::AddStylesheet('css/font-awesome.css');
		// Since the IE 7 version must be wrapped in the IE conditional... I need to manually resolve the asset.
		CurrentPage::AddStylesheet('<!--[if IE 7]><link rel="stylesheet" href="' . Core::ResolveAsset('css/font-awesome-ie7.css') . '"><![endif]-->');

		// And the core+ tweaks that are useful for the time being.
		CurrentPage::AddStylesheet('css/font-awesome-tweaks.css');
		return true;
	}
}
