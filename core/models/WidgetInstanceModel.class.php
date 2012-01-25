<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


/**
 * Model for WidgetInstanceModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class WidgetInstanceModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'theme' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
			'required' => true,
			'comment' => 'The theme to display on.'
		),
		'template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
			'required' => true,
			'comment' => 'The template name on which to display on.'
		),
		'widgetarea' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
			'required' => true,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => '10',
			'null' => false,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'default' => '*',
			'comment' => '',
			'null' => false,
		),
		'container' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
	);

	
	/**
	 * Get an array of all the parts of this request, including:
	 * 'controller', 'method', 'parameters', 'baseurl', 'rewriteurl'
	 * 
	 * @return array
	 */
	public function splitParts(){
		$ret = WidgetModel::SplitBaseURL($this->get('baseurl'));
		
		// No?
		if(!$ret){
			$ret = array(
				'controller' => null, 'method' => null, 'parameters' => null, 'baseurl' => null
			);
		}
		
		// Tack on the parameters
		if($ret['parameters'] === null) $ret['parameters'] = array();
		
		return $ret;
	}
	
	/**
	 * Execute the controller and method this page request points to.
	 * 
	 * @return View
	 */
	public function execute(){
		$pagedat = $this->splitParts();
		/// A few sanity/security checks for the controller's sake.
		
		// The controller must exist first!
		// (note, the SplitParts logic already takes care of the "Is this a valid controller" logic)
		if(!$pagedat['controller']){
			$view = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}
		
		// Any method that starts with a "_" is an internal-only method!
		if($pagedat['method']{0} == '_'){
			$view = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}
		
		// It also must be a part of the class... obviously
		if(!method_exists($pagedat['controller'], $pagedat['method'])){
			$view = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}
		
		
		// This will be a Widget object.
		$c = Widget_2_1::Factory($pagedat['controller']);
		
		// Make sure it's linked
		$c->_model = $this;
		
		$return = call_user_func(array($c, $pagedat['method']));
		if(is_int($return)){
			// A generic error code was returned.  Create a View with that code and return that instead.
			$view = new View();
			$view->error = $return;
			return $view;
		}
		elseif($return === null){
			// Hopefully it's setup!
			$return = $c->getView();
		}
		// No else needed, else it's a valid object.
		
		
		// Try to guess the templatename if it wasn't set.
		if($return->error == View::ERROR_NOERROR && $return->contenttype == View::CTYPE_HTML && $return->templatename === null){
			$cnameshort = (strpos($pagedat['controller'], 'Widget') == strlen($pagedat['controller']) - 6) ? substr($pagedat['controller'], 0, -6) : $pagedat['controller'];
			$return->templatename = strtolower('/widgets/' . $cnameshort . '/' . $pagedat['method'] . '.tpl');
		}
		
		return $return;
	}

} // END class WidgetInstanceModel extends Model
