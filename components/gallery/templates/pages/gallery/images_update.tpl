<form id="uploadsource" action='{link link="/gallery/images/update/`$album.id`?image=`$image.id`"}' method="POST" enctype="multipart/form-data" target="uploadframe">
	{$form->render('body')}
	<input type="submit" value="{$savetext}"/>
</form>


<script>
	$(function(){

		var $uploadsource = $('#uploadsource'),
				$uploadframe = null,
				debugframe = false,
				$inp = $uploadsource.find('input[type=file]'),
				$sub = $uploadsource.find('input[type=submit]');

		// Because firefox doesn't like reloading the page without asking the user to resubmit the page... :/
		if(debugframe){
			$uploadframe = $('<iframe style="position:fixed; top:0; left:0; background:white; border:2px solid orange; width:300px; height:100px;" id="uploadframe" name="uploadframe" src="about:blank"></iframe>');
		}
		else{
			$uploadframe = $('<iframe style="width:1px; height:1px; left:-90px; position:absolute;" id="uploadframe" name="uploadframe" src="about:blank"></iframe>');
		}

		$uploadsource.append($uploadframe);

		$uploadsource.submit(function(){
			// Don't try to upload another if it's already uploading.
			if($sub.val() != 'Upload') return false;

			$sub.val('Loading...');
		});

		$uploadframe.load(function(){
			var $body = $("#uploadframe").contents().find('body'),
				$error = $body.find('#error'),
				$url = $body.find('#url'),
				$id = $body.find('#imageid');

			$sub.val('Upload');
			$inp.val('');

			if($error.length){
				alert($error.text());
				return;
			}
			// Only reload if it's not an empty string... if so nothing was actually loaded.
			else if($body.text() != ''){
				// Only reload if debug is set to false... useful for debugging.
				if(!debugframe) window.location.reload();
			}
		});

		$('.primary-toggle').click(function(){
			$.ajax({
				url: Core.ROOT_WDIR + 'galleryadmin/images/setprimary/{$album.id}',
				data: { image: $(this).val() },
				type: 'post'
			});
		});

		$('#images').find('.delete').click(function(){
			if(confirm('Delete Image?')){
				$.ajax({
					url: Core.ROOT_WDIR + 'galleryadmin/images/delete/{$album.id}',
					data: { image: $(this).attr('image') },
					type: 'post',
					success: function(){ window.location.reload(); }
				});
			}
		});
	});
</script>