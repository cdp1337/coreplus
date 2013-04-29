<h3>Updates</h3>

<div id="updater-check-widget">
	<noscript>Please enable javascript to check for updates, or {a href="/updater"}check manually{/a}</noscript>
</div>


{script location="foot"}<script type="text/javascript">
	$target = $('#updater-check-widget');

	$target.html('Checking for updates...');

	$.ajax({
		url: Core.ROOT_WDIR + 'updater/check.json',
		type: 'get',
		dataType: 'json',
		success: function(d){
			if(d){
				$target.html('<a href="' + Core.ROOT_WDIR + 'updater">Updates Available!</a>').show();
			}
			else{
				$target.html('No updates available');
				// And hide this widget.
				$target.closest('.widget').hide();
			}
		},
		error: function(){
			$target.html('An error occurred while checking for updates.');
		}
	});
</script>{/script}