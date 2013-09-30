<?php
/**
 * Enter a meaningful file description here!
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130218.0859
 * @package PackageName
 *
 * Created with JetBrains PhpStorm.
 */
/**
 * Class description here
 */
class UpdaterWidget extends Widget_2_1{
	public function check(){
		if( !\Core\user()->checkAccess('g:admin') ) return '';
	}
}
