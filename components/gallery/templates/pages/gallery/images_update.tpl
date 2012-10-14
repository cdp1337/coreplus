<form id="uploadsource" action='{link link="/gallery/images/update/`$album.id`?image=`$image.id`"}' method="POST" enctype="multipart/form-data">
	{$form->render('body')}
	<input type="submit" value="{$savetext}"/>
</form>


{script library="jqueryui.timepicker"}{/script}
<script>
	$(function(){
		$('#formtextinput-model-datetaken').datetimepicker({
			dateFormat: "yy:mm:dd",
			timeFormat: 'hh:mm:00'
		});
	});
</script>