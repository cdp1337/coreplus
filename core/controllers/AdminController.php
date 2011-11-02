<?php

class AdminController extends Controller {
	
	public static $AccessString = 'g:admin';
	
	public static function Index(View $view) {
		$pages = PageModel::Find(array('admin' => '1'));
		$viewable = array();
		foreach($pages as $p){
			if(!Core::User()->checkAccess($p->get('access'))) continue;
			
			$viewable[] = $p;
		}
		$view->assignVariable('links', $viewable);
	}
	
	public static function ReinstallAll(View $page){
		// Just run through every component currently installed and reinstall it.
		// This will just ensure that the component is up to date and correct as per the component.xml metafile.
		
		$changes = array();
		
		foreach(ThemeHandler::GetAllThemes() as $t){
			if(!$t->isInstalled()) continue;
			
			if($t->reinstall()){
				$changes[] = 'Reinstalled theme ' . $t->getName();
			}
		}
		
		foreach(ComponentHandler::GetAllComponents() as $c){
			if(!$c->isInstalled()) continue;
			
			if($c->reinstall()){
				$changes[] = 'Reinstalled component ' . $c->getName();
			}
		}
		
		// Flush the system cache, just in case
		Core::Cache()->flush();
		
		//$page->title = 'Reinstall All Components';
		$page->access = 'g:admin';
		$page->assign('changes', $changes);
	}
	
	public static function Config(View $view){
		$configs = ConfigModel::Find(array(), null, 'key');
		$groups = array();
		foreach($configs as $c){
			// Export out the group for this config option.
			$gname = substr($c->get('key'), 1);
			$gname = ucwords(substr($gname, 0, strpos($gname, '/')));
			
			if(!isset($groups[$gname])) $groups[$gname] = new FormGroup(array('title' => $gname, 'class' => 'collapsible collapsed'));
			
			// /user/displayname/displayoptions
			$title = substr($c->get('key'), strlen($gname)+2);
			$val = $c->get('value')? $c->get('value') : $c->get('default_value');
			$name = 'config[' . $c->get('key') . ']';
			
			switch($c->get('type')){
				case 'string':
					$el = FormElement::Factory('text');
					break;
				case 'enum':
					$el = FormElement::Factory('select');
					$el->set('options', array_map('trim', explode('|', $c->get('options'))));
					break;
				case 'boolean':
					$el = FormElement::Factory('radio');
					$el->set('options', array('false' => 'No/False', 'true' => 'Yes/True'));
					if($val == '1' || $val == 'true' || $val == 'yes') $val = 'true';
					else $val = 'false';
					break;
				case 'int':
					$el = FormElement::Factory('text');
					$el->validation = '/^[0-9]*$/';
					$el->validationmessage = $gname . ' - ' . $title . ' expects only whole numbers with no puncuation.';
					break;
				case 'set':
					$el = FormElement::Factory('checkbox');
					$el->set('options', array_map('trim', explode('|', $c->get('options'))));
					$val = array_map('trim', explode('|', $val));
					$name = 'config[' . $c->get('key') . '][]';
					break;
				default:
					throw new Exception('Supported configuration type for ' . $c->get('key') . ', [' . $c->get('type') . ']');
					break;
			}
			
			$el->set('title', $title);
			$el->set('name', $name);
			$el->set('value', $val);
			
			$desc = $c->get('description');
			if($c->get('default_value') && $desc) $desc .= ' (default value is ' . $c->get('default_value') . ')';
			elseif($c->get('default_value')) $desc = 'Default value is ' . $c->get('default_value');
			$el->set('description', $desc);
			
			$groups[$gname]->addElement($el);
		}
		
		
		$form = new Form();
		$form->set('callsmethod', 'AdminController::_ConfigSubmit');
		foreach($groups as $g){
			$form->addElement($g);
		}
		
		$form->addElement('submit', array('value' => 'Save'));
		
		$view->assign('form', $form);
	}

	
	public static function _ConfigSubmit(Form $form){
		$elements = $form->getElements();
		
		$updatedcount = 0;
		
		foreach($elements as $e){
			// I'm only interested in config options.
			if(strpos($e->get('name'), 'config[') === false) continue;
			
			// Make the name usable a little.
			$n = $e->get('name');
			if(($pos = strpos($n, '[]')) !== false) $n = substr($n, 0, $pos);
			$n = substr($n, 7, -1);
			
			// And get the config object
			$c = new ConfigModel($n);
			
			switch($c->get('type')){
				case 'string':
				case 'enum':
				case 'boolean':
				case 'int':
					$c->set('value', $e->get('value'));
					break;
				case 'set':
					$c->set('value', implode('|', $e->get('value')));
					break;
				default:
					throw new Exception('Supported configuration type for ' . $c->get('key') . ', [' . $c->get('type') . ']');
					break;
			}
			
			if($c->save()){
				$updatedcount++;
			}
		}
		
		if($updatedcount == 0){
			Core::SetMessage('No configuration options changed', 'info');
		}
		elseif($updatedcount == 1){
			Core::SetMessage('Updated ' . $updatedcount . ' configuration option', 'success');
		}
		else{
			Core::SetMessage('Updated ' . $updatedcount . ' configuration options', 'success');
		}
		
		return '/';
	}
}
