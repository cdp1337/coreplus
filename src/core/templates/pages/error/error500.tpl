<h1>Server Error</h1>

<p class="message-error">There was a sever error and the administrator has been notified.</p>

{if $smarty.const.DEVELOPMENT_MODE && isset($exception)}
	<pre class="xdebug-var-dump">
	Error Code: {$exception->getCode()}
	Error Message: {$exception->getMessage()}
	Trace:
{$exception->getTraceAsString()}
	</pre>
{/if}
