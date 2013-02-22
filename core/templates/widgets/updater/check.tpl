<div id="updater-check-widget" style="display:none;">
	Checking for updates...
</div>


{script location="foot"}<script type="text/javascript">
	$target = $('#updater-check-widget');

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
			}
		},
		error: function(){
			$target.html('An error occurred while checking for updates.');
		}
	});
</script>{/script}