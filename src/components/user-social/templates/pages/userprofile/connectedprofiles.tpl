<form action="" method="POST">

	<table>
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th>Type</th>
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
				<select name="type[]" class="type-select">
					<option value="">-- Profile Type --</option>
					<!--<option value="blog">Blog</option>-->
					<option value="facebook">Facebook</option>
					<option value="github">Git Hub</option>
					<option value="google-plus">Google Plus</option>
					<option value="linkedin">LinkedIn</option>
					<option value="twitter">Twitter</option>
					<option value="pinterest">Pinterest</option>
					<option value="play">Youtube</option>
					<option value="link">Other Link</option>
					<option value="bolt">Generic Bolt</option>
					<option value="star">Generic Star</option>
					<option value="film">Generic Film</option>
				</select>
			</td>

			<td>
				<input type="text" name="url[]" class="url-text"/>
			</td>

			<td>
				<input type="text" name="title[]" class="title-text"/>
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



{script library="jqueryui"}{/script}
{script location="foot"}<script type="text/javascript">
	$(function(){
		var template = $('.template').html(),
			profiles, i, $el;


		$('#dest').delegate('.add-button', 'click', function(){
			$('#dest').append(template);

			return false;
		});

		$('#dest').delegate('.remove-button', 'click', function(){
			$(this).closest('tr.record').remove();

			if($('#dest').find('tr.record').length == 0){
				$('.add-button').click();
			}

			return false;
		});

		// Enable the sortable
		$('#dest').sortable({
			handle: '.icon-reorder'
		});

		// Add the elements from the pageload.
		profiles = {if $profiles}{$profiles_json}{else}null{/if};

		if(profiles){
			for(i=0; i<profiles.length; i++){
				$el = $(template);
				// To make it usable by the DOM...
				$('#dest').append($el);
				// And set the values on it.
				$el.find('.type-select').val(profiles[i].type);
				$el.find('.url-text').val(profiles[i].url);
				$el.find('.title-text').val(profiles[i].title);
			}
		}
		else{
			// Add the initial one.
			$('#dest').append(template);
		}
	});
</script>{/script}