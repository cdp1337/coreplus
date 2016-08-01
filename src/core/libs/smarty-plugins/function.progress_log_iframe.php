<?php

function smarty_function_progress_log_iframe($params, $smarty){

	$logname = isset($params['name']) ? $params['name'] : 'progress-log';
	$formid = isset($params['form']) ? $params['form'] : 'progress-log-form';
	/** @var View $view */
	$view   = \Core\view();

	$html = <<<EOD
<div id="$logname-container" style="display:none;" class="progress-log-wrapper">
	<div class="progress-log-title"></div>
	<a href="#" class="progress-log-view-details">View Details</a>
	<div class="progress-log-progressbar">
		<div class="progress-log-progressbar-inner"></div>
	</div>
	<p class="progress-log-message"></p>
	
	<iframe id="$logname-frame" name="$logname" class="progress-log-iframe" style="display:none;"></iframe>
</div>
EOD;

	$script = <<<EOD
<script>
\$(function(){ Core.ProgressLogIframe('$logname', '$formid'); });
</script>
EOD;

	\JQuery::IncludeJQuery();
	$view->addScript('assets/js/core.progress-log-iframe.js', 'head');
	$view->addScript($script, 'foot');
	return $html;
}
