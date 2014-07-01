{*
<h2>Overview</h2>
<ul>
	<li>Added: {$added}</li>
	<li>Updated: {$updated}</li>
	<li>Skipped: {$skipped}</li>
	<li>Deleted: {$deleted}</li>
</ul>

<h2>Changelog</h2>
{if $changelog}
	{$changelog}
{else}
	No changes
{/if}
*}

<iframe id="import-log" name="import-log" width="600" height="400"></iframe>

<form action="" method="post" target="import-log" id="import-form">

</form>

{script library="jquery"}{/script}
{script location="foot"}<script>
	$(function(){
		var go = null;

		$('#import-form').submit();

		go = setInterval(function(){ document.getElementById('import-log').contentWindow.scrollBy(0,20); }, 100);

		$('#import-log').load(function(){
			clearInterval(go);
			document.getElementById('import-log').contentWindow.scrollBy(0,200);
		});
	});
</script>{/script}