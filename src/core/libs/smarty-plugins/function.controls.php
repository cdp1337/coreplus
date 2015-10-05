<?php
/**
 * @package Core\Templates\Smarty
 * @since 2.4.0
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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
 */

/**
 * Render a UL of control links for a given Model.
 *
 * Will call the /core/controllinks/{baseurl} hook automatically to retrieve any addon calls.
 *
 * #### Smarty Parameters
 *
 * * model
 *   * Preferred way to use this method, simply pass the model to retrieve the control links from.
 *   * This MUST be a valid Model and calls the getControlLinks method of that model.
 * * baseurl
 *   * String of the "baseurl" or the model or object to view.
 *   * This relies on a hook being dispatched on /core/controllinks/{baseurl}.
 * * subject
 *   * If baseurl is requested, this can be an ID, string, object, or anything else that the hook should pass along with the request.
 * * hover
 *   * Set to "0" to disable hover functionality in the UI.
 * * proxy-force
 *   * Set to "0" to disallow a proxy and "1" to force a proxy.
 * * proxy-text
 *   * Set the proxy text to a given value
 *
 * #### Example Usage
 *
 * Shortened, inline version of the model controls and the /core/controllinks hook.
 * This is the most ideal use of this function.
 *
 * This version will first query the Model's getControlLinks method,
 * then the appropriate /core/controllinks hook for any additional links.
 *
 * <pre>
 * {controls model=$user}
 * </pre>
 *
 * Traditional usage of the controls and the /core/controllinks hook.
 *
 * <pre>
 * {controls baseurl="/user/view" subject="`$user.id`"}
 * </pre>
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @throws SmartyException
 *
 * @return string
 */
function smarty_function_controls($params, $smarty){

	if(isset($params['model'])){
		// There is a "model" attribute provided, this must be a valid Model object,
		// (and is the preferred way of handling this system).
		$subject = $params['model'];
		if(!$subject instanceof Model){
			throw new SmartyException('Only Models can be used with the {controls model=...} syntax!');
		}

		$controls = ViewControls::DispatchModel($subject);
	}
	elseif(isset($params['baseurl'])){
		// There is a baseurl provided, this does not require a full object and simply a string will suffice.
		// Since there is no Model provided, only the registered hooks will be called.
		$baseurl = $params['baseurl'];

		// They may or may not have subjects.
		// The subject is the subject matter of this control link.
		$subject = (isset($params['subject'])) ? $params['subject'] : null;

		$controls = ViewControls::Dispatch($baseurl, $subject);
	}
	else{
		throw new SmartyException('Unable to get links without a baseurl!  Provided Parameters: ' . print_r($params, true));
	}

	// Other options
	if(isset($params['hover'])){
		$controls->hovercontext = ($params['hover']);
	}

	if(isset($params['proxy-force'])){
		$controls->setProxyForce($params['proxy-force']);
	}

	if(isset($params['proxy-text'])){
		$controls->setProxyText($params['proxy-text']);
	}

	// Render out controls.
	echo $controls->fetch();
}