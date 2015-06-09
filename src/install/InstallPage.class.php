<?php
/**
 * Rudimentary template and page system that's used in the installer.
 *
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * Copyright (C) 2010  Charlie Powell <charlie@evalagency.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 *
 * @package Core\Installer
 * @author Charlie Powell <charlie@evalagency.com>
 * @deprecated 2013.04 - This file has been deprecated and is a candidate for removal very soon.
 */

class _InstallTemplate {
	/**
	 * The template to resolve with.
	 * 
	 * @var string
	 */
	public $template = null;
	
	/**
	 * Any variables that have been assigned to the template.
	 */
	protected $_vars = array();

	public function assign($var, $value){
		$this->_vars[$var] = $value;
	}
	
	public function fetch(){
		$in = file_get_contents($this->template);

		// Replace the varaibles in the appropriate places.
		foreach($this->_vars as $k => $v){
			$in = str_replace('%' . $k . '%', $v, $in);
		}

		// Allow for basic logic in the template.
		//preg_replace('/%if\(([^\)]*)\)%(.*)%\/if%/emU', 'return ($1)? $2 : "";', $in);
		//preg_replace('/%if\((.*)\)%(.*)%fi%/eU', '(str_replace(\'\"\', \'"\', $1))? "$2" : "";', $in);
		
		$in = preg_replace('/\{if\((.*)\)\}(.*)\{\/if\}/eUis', '(eval("return ($1);"))? "$2" : "";', $in);
		
		return $in;
	}

}

class _InstallSkin extends _InstallTemplate{
	public function __construct(){
		// Some default variables.
		$this->_vars = array(
			'head' => '',
			'title' => 'Installation of Core Plus',
			'error' => ''
		);
		
		$this->template = 'templates/skin.tpl';
	}
}

/**
 * Just a stupid-simple template system that's as minimalistic as possible.
 * 
 * To use it, instantiate the page, give it a template, assign some variables and render!
 */
class InstallPage extends _InstallTemplate{
	
	private $_skin = null;
	
	private function _getSkin(){
		if($this->_skin === null){
			$this->_skin = new _InstallSkin();
		}
		return $this->_skin;
	}

	public function assign($var, $value){
		parent::assign($var, $value);
		
		switch($var){
			case 'title':
			case 'error':
				$this->_getSkin()->assign($var, $value);
				break;
		}
	}
	
	public function render(){
		$body = $this->fetch();
		
		// Create a template for the skin and embed the body into that.
		$skin = $this->_getSkin();
		$skin->assign('body', $body);
		echo $skin->fetch();

		// Once the page is rendered stop execution.
		die();
	}
}
