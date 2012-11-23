<?php

/**
 * Function responsible for displaying a <ul/> of control links for a given object.
 *
 * Will call the /core/controllinks/{baseurl} hook automatically to retrieve any addon calls.
 *
 * @param Array  $params Associative array of parameters passed in
 *               'baseurl' REQUIRED The "baseurl" of this controllink object.
 *               'subject' [optional] The subject to pass into the hook dispatch to accompany the baseurl, optional, but some objects may require one.
 * @param Smarty $template
 *
 * @throws SmartyException
 */
function smarty_function_controls($params, $template){
	// The params here are baseurl which is used by the hook system, and
	// the subject

	// {controls baseurl="/user/view" subject="`$user.id`"}

	if(!isset($params['baseurl'])){
		throw new SmartyException('Unable to get links without a baseurl');
	}

	$baseurl = $params['baseurl'];

	// They may or may not have subjects.
	// The subject is the subject matter of this control link.
	$subject = (isset($params['subject'])) ? $params['subject'] : null;

	$links = HookHandler::DispatchHook('/core/controllinks' . $baseurl, $subject);

	$controls = new ViewControls();
	$controls->addLinks($links);

	echo $controls->fetch();
}