<h1>And... you broke it!</h1>

<p>Actually I guess we did, a server error was encountered and the administrator notified.</p>

{if $smarty.const.DEVELOPMENT_MODE && isset($exception)}
	<pre class="xdebug-var-dump">
	Error Code: {$exception->getCode()}
	Error Message: {$exception->getMessage()}
	Trace:
{$exception->getTraceAsString()}
	</pre>
{/if}
