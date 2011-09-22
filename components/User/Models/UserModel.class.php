<?php
/**
 * Model for UserModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class UserModel extends Model {
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'email' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
			'validation' => Model::VALIDATION_EMAIL,
		),
		'backend' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'hidden',
			'default' => 'datastore'
		),
		'password' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 60,
			'null' => false,
		),
		'apikey' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'active' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '1',
			'null' => false,
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '0',
			'null' => false,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:email' => array('email'),
	);
	
	
	public function validate($k, $v, $throwexception = false) {
		if($k == 'password'){
			$valid = true;
			// complexity check from the config
			if(strlen($v) < ConfigHandler::GetValue('/user/password/minlength')){
				$valid = 'Please ensure that the password is at least ' . ConfigHandler::GetValue('/user/password/minlength') . ' characters long.';
			}

			// complexity check from the config
			if(ConfigHandler::GetValue('/user/password/requiresymbols') > 0){
				preg_match_all('/[^a-zA-Z]/', $v, $matches); // Count a number as a symbol.  Close enough :/
				if(sizeof($matches[0]) < ConfigHandler::GetValue('/user/password/requiresymbols')){
					$valid = 'Please ensure that the password has at least ' . ConfigHandler::GetValue('/user/password/requiresymbols') . ' symbol(s) or number(s).';
				}
			}

			// complexity check from the config
			if(ConfigHandler::GetValue('/user/password/requirecapitals') > 0){
				preg_match_all('/[A-Z]/', $v, $matches);
				if(sizeof($matches[0]) < ConfigHandler::GetValue('/user/password/requirecapitals')){
					$valid = 'Please ensure that the password has at least ' . ConfigHandler::GetValue('/user/password/requirecapitals') . ' capital letter(s).';
				}
			}
			
			// Validation's good, return true!
			if($valid === true) return true;
			// Validation failed and an Exception was requested.
			elseif($throwexception) throw new ModelValidationException($valid);
			// Validation failed, but just return the message.
			else return $valid;
		}
		else{
			return parent::validate($k, $v, $throwexception);
		}
	}
	
	public function set($k, $v) {
		if($k == 'password'){
			// Password skips the validation check, as it should be hashed 
			// when it gets to this stage.
			
			$this->_data[$k] = $v;
			$this->_dirty = true;
		
			return true;
		}
		else{
			return parent::set($k, $v);
		}
	}
	
	/**
	 * Set the password for this user, automatically hashing it.
	 * 
	 * @param string $v plain text password
	 * @return boolean
	 * @throws ModelException
	 * @throws ModelValidationException
	 */
	public function setPassword($v){
		// Quick validation (since the setter ignores validation)
		// Will throw an exception and leave this script if it fails.
		$this->validate('password', $v, true);
		
		// hash the password.
		$hasher = new PasswordHash(15);
		$password = $hasher->hashPassword($v);

		// Same?
		if($this->_data['password'] == $password) return false;
		
		// Still here?  Then try to set it.
		return $this->set('password', $password);
	}
	
	public function save() {
		// Every usermodel needs to have an apikey set prior to saving.
		if(!$this->_data['apikey']){
			$this->generateNewApiKey();
		}
		
		return parent::save();
	}
	
	/**
	 * Generate a new secure API key for this user.
	 * 
	 * This is a built-in function that can be used for automated access to
	 * secured resources on the application/site. 
	 * 
	 * Will only set the config, save() still needs to be called externally.
	 * 
	 * @since 2011.08
	 */
	public function generateNewApiKey(){
		$this->set('apikey', Core::RandomHex(64, true));
	}

} // END class UserModel extends Model
