<form action="" method="post" target="reinstall-log" id="reinstall-form">
	<input type="submit" value="Click to Reinstall"/>
</form>
<br/>

<iframe id="reinstall-log" name="reinstall-log" style="display: none; width:90%; height:30em;"></iframe>

{script library="jquery"}{/script}
{script location="foot"}<script>
	$(function(){
		var go = null,
			log = document.getElementById('reinstall-log'),
			$log = $('#reinstall-log');

		// Fix the width of the iframe.
		//log.width = $('body').width() * .8;

		//$('#reinstall-form').submit();

		$('#reinstall-form').submit(function() {
			$log.show();
			go = setInterval(
				function(){
					log.contentWindow.scrollBy(0,100);
				}, 25
			);
		});

		$log.load(function(){
			clearInterval(go);
			log.contentWindow.scrollBy(0,5000);
		});
	});
</script>{/script}
