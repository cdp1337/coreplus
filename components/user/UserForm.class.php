<?php

class FormAccessStringInput extends FormElement{
	private $_targetname = null;
	
	public function render() {
		static $renderedcount = 0;
		
		$renderedcount++;
		$this->_targetname = '_formaccessstring' . $renderedcount . \Core\RandomHex(8);
		
		$v = trim($this->get('value'));
		$checked = 'advanced';
		$advanced_groups = array();
		$type = 'whitelist';
		
		if($v == '*'){
			$checked = 'basic_anyone';
		}
		elseif(!$v){
			// Blank value
			$checked = 'advanced';
		}
		elseif($v == 'g:anonymous'){
			$checked = 'basic_anonymous';
		}
		elseif($v == 'g:authenticated'){
			$checked = 'basic_authenticated';
		}
        elseif($v == 'none'){
            // Allow for a blank value.
            $checked = null;
            $type = null;
        }
		else{
			// Determine the sub groups checked.
			$checked = 'advanced';
			$parts = array_map('trim', explode(';', $v));
			foreach($parts as $p){
				if($p == '*'){
					// If a wildcard is present, mark the groups as ones to blacklist.
					$type = 'blacklist';
					continue;
				}
				list($t, $tv) = explode(':', $p);
				// Trim off the '!' in front of it, it'll be picked up by the presence of the '*' at the end.
				if($tv{0} == '!') $tv = substr($tv, 1);
				$advanced_groups[] = $tv;
			}
		}
		
		$groups = array();
		
		// Tack on the system groups.
		$anongroup = new Model();
		$anongroup->setFromArray(array(
			'id' => 'anonymous',
			'name' => 'Anonymous Users'
		));
		
		$authgroup = new Model();
		$authgroup->setFromArray(array(
			'id' => 'authenticated',
			'name' => 'Authenticated Users'
		));
		$groups[] = $anongroup;
		$groups[] = $authgroup;
		
		// Find all the groups currently on the site.
		$groups = array_merge($groups, UserGroupModel::Find(null, null, 'name'));
		foreach($groups as $k => $v){
			if(in_array($v->get('id'), $advanced_groups)) $v['checked'] = true;
		}
		
		
		$tpl = new Template();
		$tpl->assign('element', $this);
		$tpl->assign('groups', $groups);
		$tpl->assign('dynname', $this->_targetname);
		$tpl->assign('main_checked', $checked);
		$tpl->assign('advanced_type', $type);

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