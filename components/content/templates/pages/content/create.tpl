{$form->render()}

{script library="jquery"}{/script}
{script}
$(function(){
	$('input[name="page[title]"]').blur(function(){
		var $this = $(this),
			$target = $('input[name="page[rewriteurl]"]'),
			text,
			val = $target.val();
		
		if(val == ''){
			text = $this.val();
			// Make sure it's a valid URL string with a '/' prefix.
			$target.val('/' + text.toURL());
		}
	});
});
{/script}