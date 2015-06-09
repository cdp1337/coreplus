<?php
/**
 * Class file for the model PackageRepositoryLicenseModel
 *
 * @package Package Repository
 * @author Charlie Powell <charlie@evalagency.com>
 */
class PackageRepositoryLicenseModel extends Model {

	const VALID_VALID    = 0;
	const VALID_INVALID  = 1;
	const VALID_EXPIRED  = 2;
	const VALID_ACCESS   = 4;
	const VALID_PASSWORD = 8;

	/**
	 * Schema definition for PackageRepositoryLicenseModel
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = [
		'id' => [
			'type' => Model::ATT_TYPE_UUID,
		    'comment' => 'Also the public key to send to the client',
		],
	    'password' => [
		    'type' => Model::ATT_TYPE_STRING,
		    'form' => [
			    'description' => 'Password of this license to send to the client',
		    ],
	        'comment' => 'Password of this license to send to the client',
	    ],
	    'comment' => [
		    'type' => Model::ATT_TYPE_STRING,
		    'form' => [
			    'description' => 'Administrative comment, displayed on the listing page',
		    ],
	        'comment' => 'Administrative comment',
	    ],
	    'expires' => [
		    'type' => Model::ATT_TYPE_ISO_8601_DATE,
		    'form' => [
			    'description' => 'Date this license expires',
		    ],
	        'comment' => 'Y-m-d format of expiration date for this license',
	    ],
	    'ip_restriction' => [
		    'type' => Model::ATT_TYPE_TEXT,
		    'form' => [
			    'title' => 'IP Restriction',
		        'description' => 'Set to an IP, IP network, or newline-separated list of IPs to restrict for this license key.',
		    ],
	        'comment' => 'Single IP, single network, or newline-separated list of IPs to allow for this license key',
	    ],
	];

	/**
	 * Index definition for PackageRepositoryLicenseModel
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = [
		'primary' => ['id'],
	];

	/**
	 * Check if this license is valid, will return a group of flags if invalid.
	 *
	 * @param string $password Optional password to verify
	 * @return int
	 */
	public function isValid($password = null){
		$valid = 0;
		if(!$this->exists()) {
			return self::VALID_INVALID;
		}

		if($this->get('expires') < date('Y-m-d')){
			$valid |= self::VALID_EXPIRED;
		}

		if($this->get('ip_restriction')){
			$ips = [REMOTE_IP];
			$longip = ip2long(REMOTE_IP);
			for($i = 32; $i > 8; $i--){
				$mask = ~((1 << (32 - $i)) - 1);
				$ips[] = long2ip($longip & $mask) . '/' . $i;
			}

			$iplist = explode("\n", str_replace("\r", "", $this->get('ip_restriction')));
			$allowed = false;
			foreach($ips as $ip){
				if(in_array($ip, $iplist)){
					$allowed = true;
					break;
				}
			}
			if(!$allowed){
				$valid |= self::VALID_ACCESS;
			}
		}

		if($password && $this->get('password') != strtoupper($password)){
			$valid |= self::VALID_PASSWORD;
		}

		return $valid;
	}

	/**
	 * Get the human-readable label for this record.
	 *
	 * The parent method will sift through the schema looking for keys that appear to be human-readable terms,
	 * but for best results, please extend this method and have it return what's necessary for the given Model.
	 *
	 * @return string
	 */
	public function getLabel(){
		// @todo Have a particular key to use as the label for this model?
		// If so, have the following as necessary.
		// return $this->get('blah');

		// Otherwise, the default is fine or simply remove the method on this child.
		return parent::getLabel();
	}

	/**
	 * Get an array of control links for this model.
	 *
	 * The returned data MUST be either an empty array or an index array of arrays.
	 * Each internal array should have link, title, icon, and any other parameter supported by the ViewControl
	 *
	 * @see ViewControl.class.php
	 *
	 * @return array
	 */
	public function getControlLinks(){

		$links = [];
		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');
		if($manager && $this->exists()){
			$links[] = [
				'link' => '/packagerepositorylicense/edit/' . $this->get('id'),
			    'title' => 'Edit License',
			    'icon' => 'edit',
			];
		}
		return $links;
	}
}