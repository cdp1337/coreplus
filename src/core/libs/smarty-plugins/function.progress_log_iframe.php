<?php

function smarty_function_progress_log_iframe($params, $smarty){

	$logname = isset($params['name']) ? $params['name'] : 'progress-log';
	$formid = isset($params['form']) ? $params['form'] : 'progress-log-form';

	$html = <<<EOD
<iframe id="$logname" name="$logname" style="display: none; width:90%; height:30em;"></iframe>
EOD;

	$script = <<<EOD
<script>
\$(function(){
	var go = null,
			log = document.getElementById('$logname'),
			\$log = $('#$logname');

		// Fix the width of the iframe.
		//log.width = $('body').width() * .8;

		$('#$formid').submit(function() {
			\$log.show();
			go = setInterval(
				function(){
					log.contentWindow.scrollBy(0,100);
				}, 25
			);
		});

		\$log.load(function(){
			clearInterval(go);
			log.contentWindow.scrollBy(0,5000);
		});
	});
</script>
EOD;

	\Core\view()->addScript('jquery');
	\Core\view()->addScript($script, 'foot');
	return $html;
}
