<iframe id="reinstall-log" name="reinstall-log" style="width:90%; height:30em;"></iframe>

<form action="" method="post" target="reinstall-log" id="reinstall-form">

</form>

{script library="jquery"}{/script}
{script location="foot"}<script>
$(function(){
	var go = null,
		log = document.getElementById('reinstall-log');

	// Fix the width of the iframe.
	//log.width = $('body').width() * .8;

	$('#reinstall-form').submit();

	go = setInterval(function(){
		log.contentWindow.scrollBy(0,100);
		}, 50
	);

	$('#reinstall-log').load(function(){
		clearInterval(go);
		log.contentWindow.scrollBy(0,500);
	});
});
</script>{/script}