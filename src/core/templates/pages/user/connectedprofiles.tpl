{css}<style>
	.page-user-connectedprofiles table {
		table-layout: auto;
	}
	.icon-selector {
		width: 325px;
	}
	.icon-selector-icon {
		display: inline-block;
		width: 50px;
		text-align: center;
	}
</style>{/css}
<form action="" method="POST">

	<table>
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th>Icon</th>
			<th>URL</th>
			<th>Title</th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody id="dest">

		</tbody>
	</table>


	<br/>
	<input type="submit" value="Save Profiles"/>

</form>




<table style="display:none;">
	<tbody class="template">

		<tr class="record">

			<td>
				<i class="icon-reorder"></i>
			</td>

			<td>
				<a href="#" class="icon-change-link">
					<span class="icon-none">
						Set Icon
					</span>
					<span class="icon-set" style="display:none;">
						<span class="icon-current"></span>
						(Change Icon)
					</span>
				</a>
				<input type="hidden" name="type[%COUNTER%]" class="input-type"/>
			</td>

			<td>
				<input type="text" name="url[%COUNTER%]" class="input-url"/>
			</td>

			<td>
				<input type="text" name="title[%COUNTER%]" class="input-title"/>
			</td>

			<td>
				<a href="#" class="remove-button button" title="Remove Record">
					<i class="icon-remove"></i>
				</a>

				<a href="#" class="add-button button" title="Add Record">
					<i class="icon-add"></i>
				</a>
			</td>
		</tr>
	</tbody>
</table>

<template id="icon">
	<a href="#" data-icon="%ICON%" class="icon-selector-icon">
		<i class="icon-%ICON%"></i>
	</a>
</template>



{script library="jqueryui"}{/script}
{script location="foot"}<script type="text/javascript">
	$(function(){
		var
			$template = $('.template'),
			template, profiles, i, $el,
			icontmpl = $('#icon').html(),
			counter = 0,
			$dest = $('#dest'),
			$icons = $('<div class="icon-selector"/>'),
			$icontarget = null,
			setIcon,
			icons = [
				// Specific organization icons
				"bitbucket",
				"dropbox",
				"facebook",
				"foursquare",
				"github",
				"google-plus",
				"instagram",
				"linkedin",
				"pinterest",
				"skype",
				"stack-overflow",
				"twitter",
				"tumblr",
				"youtube",
				"xing",
				// OS icons
				"android",
				"apple",
				"linux",
				"windows",
				// Generic icons
				"bug",
				"bolt",
				"link",
				"film",
				"star"
			];

		setIcon = function(icon, $target){
			//console.log('Setting icon [' + icon + '] on target', $target);

			if($target){
				$target.find('.input-type').val(icon);
				$target.find('.icon-none').hide();
				$target.find('.icon-set').show();
				$target.find('.icon-current').html('<i class="icon-' + icon + '"></i>');
			}
		};

		// Build the icon selector dialog and all the icons included.
		$('body').append($icons);

		for(i in icons){
			$icons.append(icontmpl.replace(/%ICON%/g, icons[i]));
		}

		$icons.dialog({
			title: 'Select Icon',
			autoOpen: false,
			modal: true
		});

		$icons.find('.icon-selector-icon').click(function() {
			setIcon(
				$(this).data('icon'),
				$icontarget
			);
			$icons.dialog('close');
		});

		// Trigger the link to open said icon dialog
		$dest.on('click', '.icon-change-link', function() {
			$icons.dialog('open');
			$icontarget = $(this).closest('td');
			return false;
		});

		// Trigger to add a new record
		$dest.on('click', '.add-button', function(){
			++counter;

			$dest.append($template.html().replace(/%COUNTER%/g, counter));

			return false;
		});

		// Trigger to remove an existing record
		$dest.on('click', '.remove-button', function(){
			$(this).closest('tr.record').remove();

			if($dest.find('tr.record').length == 0){
				counter = 1;
				$dest.append($template.html().replace(/%COUNTER%/g, counter));
			}

			return false;
		});

		// Trigger to set the icon automatically if not one set otherwise
		$dest.on('blur', '.input-url', function() {
			var $this = $(this),
				$el = $this.closest('tr'),
				u = $this.val(),
				i, e;

			if(!$el.find('.input-type').val()){
				u = u.replace(/[^a-z]/g, '');
				for(i in icons){
					e = icons[i].replace(/[^a-z]/g, '');
					if(u.indexOf(e) != -1){
						setIcon(icons[i], $el);
						return true;
					}
				}

				 // A few special exceptions.
				if(u.indexOf('plusgooglecom') != -1){
					setIcon('google-plus', $el);
					return true;
				}
			}
			return true;
		});

		// Enable the sortable
		$dest.sortable({
			handle: '.icon-reorder'
		});

		// Add the elements from the pageload.
		profiles = {if $profiles}{$profiles_json}{else}null{/if};

		if(profiles){
			for(i=0; i<profiles.length; i++){
				++counter;

				$el = $($template.html().replace(/%COUNTER%/g, counter));
				// To make it usable by the DOM...
				$dest.append($el);
				// And set the values on it.
				setIcon(profiles[i].type, $el);
				//$el.find('.input-type[value="' + profiles[i].type + '"]').attr('checked', 'checked');
				$el.find('.input-url').val(profiles[i].url);
				$el.find('.input-title').val(profiles[i].title);
			}
		}
		else{
			// Add the initial one.
			$dest.append($template.html().replace(/%COUNTER%/g, counter));
		}
	});
</script>{/script}