<?php
/**
 * Class file for the model FormBuilderModel
 *
 * @package FormBuilder
 * @author Nicholas Hinsch <nicholas@eval.bz>
 */
class FormBuilderModel extends Model {
	/**
	 * Schema definition for FormModel
	 * @todo Fill this in with your model structure
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = array(
		'id' => [
			'type' => Model::ATT_TYPE_UUID,
		],
		'site' => array(
			'type' => Model::ATT_TYPE_SITE,
			'formtype' => 'system',
		),
		'notify_email' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'required' => false,
			'form' => array(
				'title' => 'Administrative Email Addresses',
				'description' => 'List of email(s) to send submissions to, list multiple emails by a new line or space',
				'group' => 'Access & Advanced',
				'grouptype' => 'tabs',
			),
		),
		'editpermissions' => array(
			'type' => Model::ATT_TYPE_STRING,
			'default' => '!*',
			'form' => array(
				'type' => 'access',
				'title' => 'Edit Permissions',
				'description' => 'Permissions for who is allowed to edit this page and list the entries',
				'group' => 'Access & Advanced',
				'grouptype' => 'tabs',
			)
		),
		'listpermissions' => array(
			'type' => Model::ATT_TYPE_STRING,
			'default' => '!*',
			'form' => array(
				'type' => 'access',
				'title' => 'Listing Permissions',
				'description' => 'Permissions for who is allowed to list the submissions',
				'group' => 'Access & Advanced',
				'grouptype' => 'tabs',
			)
		),
		'has_captcha' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0,
			'form' => array(
				'title' => 'Should this form require a captcha field?',
				'group' => 'Basic',
				'grouptype' => 'tabs',
			),
		),
		'confirmation_text' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'form' => array(
				'type' => 'wysiwyg',
				'title' => 'Successful confirmation content',
				'description' => 'The text that is displayed on the "thank you" page',
				'group' => 'Confirmation / Thank You',
				'grouptype' => 'tabs',
			)
		),
		'thankyou_email' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'form' => array(
				'type' => 'wysiwyg',
				'title' => 'Thank You Email Text',
				'description' => 'The text that is displayed on the confirmation email.  If left blank, no email is sent to the user.',
				'group' => 'Confirmation / Thank You',
				'grouptype' => 'tabs',
			)
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

	/**
	 * Index definition for FormModel
	 * @todo Fill this in with your model indexes
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);

	public function __construct($key = null)
	{
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
			'FormBuilderEntry' => array(
				'link' => Model::LINK_HASMANY,
				'on' => array('formid' => 'id'),
			),
			'Widget' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
		);

		parent::__construct($key);
	}

	public function get($k) {
		$k = strtolower($k);
		switch ($k) {
			case 'baseurl':
				return '/formbuilder/view/' . $this->_data['id'];
				break;
			case 'title':
				return $this->getLink('Page')->get('title');
				break;
			case 'rewriteurl':
				return $this->getLink('Page')->get('rewriteurl');
				break;
			default:
				return parent::get($k);
		}
	}

	/**
	 * Get the directory to upload images to, excluding the public/private component.
	 *
	 * @return mixed
	 */
	public function getUploadDirectory(){
		// Determine the directory to upload to.  This is just a nit-picky backend thing.
		// This will keep the files organized into their own individual directories (for each album)
		$dir = $this->getLink('Page')->get('title');
		// Trim off any invalid characters
		$dir = \Core\str_to_url($dir);
		// And the directory character.
		$dir = str_replace('/', '', $dir);

		return $dir . '/';
	}

	/**
	 * Get the directory to upload images to, including the public/private component.
	 *
	 * @return mixed
	 */
	public function getFullUploadDirectory(){
		return 'public/formbuilder/' . $this->getUploadDirectory();
	}
}