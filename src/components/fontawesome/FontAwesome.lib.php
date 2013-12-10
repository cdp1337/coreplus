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
		\Core\view()->addStylesheet('css/font-awesome.css');
		// Since the IE 7 version must be wrapped in the IE conditional... I need to manually resolve the asset.
		//\Core\view()->addStylesheet('<!--[if IE 7]><link rel="stylesheet" href="' . Core::ResolveAsset('css/font-awesome-ie7.css') . '"><![endif]-->');

		return true;
	}
}
