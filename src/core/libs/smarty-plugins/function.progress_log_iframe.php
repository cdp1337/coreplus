<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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
	<p class="progress-log-warnings"></p>
	
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
