<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContentModel
 *
 * @author powellc
 */
class NavigationEntryModel extends Model{
	
	/**
	 * Based on the type of this entry, ie: int or ext, resolve the URL fully.
	 * 
	 * @return string
	 */
    public function getResolvedURL(){
		switch($this->get('type')){
			case 'int':
				return Core::ResolveLink($this->get('baseurl'));
				break;
			case 'ext':
				if(strpos(substr($this->get('baseurl'), 0, 6), '://') !== false) return $this->get('baseurl');
				else return 'http://' . $this->get('baseurl');
				break;
		}
	}
}
?>
