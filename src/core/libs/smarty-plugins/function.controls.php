<?php
/**
 * Function responsible for displaying a <ul/> of control links for a given object.
 *
 * Will call the /core/controllinks/{baseurl} hook automatically to retrieve any addon calls.
 *
 * <h3>Example Usage</h3>
 *
 * <pre><code>{controls baseurl="/user/view" subject="`$user.id`"}</code></pre>
 * <p>
 * Traditional usage of the controls and the /core/controllinks hook.
 * </p>
 *
 * <pre><code>{controls model=$user}</code></pre>
 * <p>
 * Shortened, inline version of the model controls and the /core/controllinks hook.
 * </p>
 * <p>
 * This version will first query the Model's getControlLinks method,
 * then the appropriate /core/controllinks hook for any additional links.
 * </p>
 * <p>
 * This enables a shorter version of the control hook system for application developers,
 * since they no longer need to use a hook call for the base links.
 * </p>
 *
 * @param Array  $params Associative array of parameters passed in
 *               'baseurl' REQUIRED The "baseurl" of this controllink object.
 *               'subject' [optional] The subject to pass into the hook dispatch to accompany the baseurl, optional, but some objects may require one.
 * @param Smarty $template
 *
 * @throws SmartyException
 */
function smarty_function_controls($params, $template){

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