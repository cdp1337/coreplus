{assign var='type' value='text'}
{include file="includes/forms/_standard_elements.tpl"}

{if Core::IsLibraryAvailable('jquery')}
	{script library='jquery'}{/script}
	{script library="Core.Strings"}{/script}

	{script location="foot"}<script>
		(function(){
			var rewritename = "{$element->get('name')}",
				titlename = rewritename.replace('[rewriteurl]', '[title]'),
				parentname = rewritename.replace('[rewriteurl]', '[parenturl]'),
				$title = $('input[name="' + titlename + '"]'),
				$parent = $(':input[name="' + parentname + '"]'),
				$target = $('input[name="' + rewritename + '"]'),
				keepsync = false, geturl;


			geturl = function(){
				var text, opt;

				if($parent.length != 0){
					opt = $parent.find('option:selected').text();
					if(opt.match(/\( .* \)/)){
						text = opt.replace(/^.*\( (.*) \).*$/, '$1') + '/' + $title.val().toURL();
					}
					else{
						text = '/' + $title.val().toURL();
					}
				}
				else{
					text = '/' + $title.val().toURL();
				}

				return text;
			};


			// First of all, check to see if the url should be kept in sync at all.
			if($target.val() == '' || $target.val() == geturl()) keepsync = true;

			$title.blur(function(){
				if(!keepsync) return;
				$target.val(geturl());
			});

			$parent.change(function(){
				if(!keepsync) return;
				$target.val(geturl());
			});

			$target.blur(function(){
				if($target.val() == ''){
					keepsync = true;
					$target.val(geturl());
					return;
				}
				else if($target.val() == geturl()){
					keepsync = true;
					return;
				}
				else{
					keepsync = false;
					return;
				}
			});

		})();
	</script>{/script}
{/if}
