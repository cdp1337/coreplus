<!--<p>
	To access and enable/disable your API keys, use the <a href="https://code.google.com/apis/console" target="_BLANK">Google API dashboard</a>.
</p>-->


<template id="analytics-template">
	<p class="message-tutorial">
		Access your <a href="https://www.google.com/analytics" target="_blank">Google Analytics Dashboard</a>
		and copy/paste the appropriate key.
		<br/><br/>
		If you need to create a new web property, click on their 'Admin' link, select the desired property group,
		and click 'New Property'.
	</p>
</template>


{$form->render()}


{script location="foot"}<script>
	$(function(){
		$('#formtabsgroup-analytics').prepend($('#analytics-template').html());
	});
</script>{/script}