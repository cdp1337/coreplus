<?php
/**
 * @package Core\Templates\Smarty
 * @since 2.4.0
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
		$subject = $params['model'];
		if(!$subject instanceof Model){
			throw new SmartyException('Only Models can be used with the {controls model=...} syntax!');
		}
		$baseurl = '/' . strtolower(get_class($subject));
	}
	elseif(isset($params['baseurl'])){
		$baseurl = $params['baseurl'];

		// They may or may not have subjects.
		// The subject is the subject matter of this control link.
		$subject = (isset($params['subject'])) ? $params['subject'] : null;
	}
	else{
		throw new SmartyException('Unable to get links without a baseurl!');
	}

	// Hover should be the default behaviour.
	if(isset($params['hover'])){
		$hover = ($params['hover']);
	}
	else{
		$hover = true;
	}

	$firstlinks = ($subject instanceof Model) ? $subject->getControlLinks() : [];
	$additionallinks = HookHandler::DispatchHook('/core/controllinks' . $baseurl, $subject);

	$links = array_merge($firstlinks, $additionallinks);

	$controls = new ViewControls();
	$controls->hovercontext = $hover;
	$controls->addLinks($links);

	echo $controls->fetch();
}