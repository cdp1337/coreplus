{assign var='type' value='text'}
{include file="forms/elements/_standard_elements.tpl"}

{if Core::IsLibraryAvailable('jquery')}
	{script library='jquery'}{/script}
	{script library="Core.Strings"}{/script}

	{script location="foot"}
		(function(){
			var rewritename = "{$element->get('name')}",
				titlename;

			titlename = rewritename.replace('[rewriteurl]', '[title]');
			$('input[name="' + titlename + '"]').blur(function(){
				var $this = $(this),
					$target = $('input[name="' + rewritename + '"]'),
					text,
					val = $target.val();

				if(val == ''){
					text = $this.val();
					// Make sure it's a valid URL string with a '/' prefix.
					$target.val('/' + text.toURL());
				}
			});

		})();
	{/script}
{/if}
