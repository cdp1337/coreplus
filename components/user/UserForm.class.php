<?php

class FormAccessStringInput extends FormElement{
	private $_targetname = null;
	
	public function render() {
		static $renderedcount = 0;
		
		$renderedcount++;
		$this->_targetname = '_formaccessstring' . $renderedcount;
		
		// Find all the groups currently on the site.
		$groups = UserGroupModel::Find(null, null, 'name');
		
		$tpl = new Template();
		$tpl->assign('element', $this);
		$tpl->assign('groups', $groups);
		$tpl->assign('dynname', $this->_targetname);

		return $tpl->fetch($this->getTemplateName());
	}
	
	public function lookupValueFrom(&$src) {
		// I'll let the parent do all the work.
		$val = parent::lookupValueFrom($src);
		
		// In addition, I have to sift through the rest of the options for this system.
		switch($val){
			case 'basic_anyone':
				return '*';
			case 'basic_anonymous':
				return 'g:anonymous';
			case 'basic_authenticated':
				return 'g:authenticated';
			case '':
				return null;
		}
		
		// No?  alright, must be advanced...
		if(!isset($src[$this->_targetname . '_type'])) $src[$this->_targetname . '_type'] = 'whitelist';
		
		$bool = ($src[$this->_targetname . '_type'] == 'whitelist') ? '' : '!';
		$groups = array();
		// Get each group selected
		foreach($src[$this->_targetname] as $g){
			$groups[] = 'g:' . $bool . $g;
		}
		// And the white/black list itself.
		if($src[$this->_targetname . '_type'] != 'whitelist') $groups[] = '*';
		
		return implode(';', $groups);
	}
}