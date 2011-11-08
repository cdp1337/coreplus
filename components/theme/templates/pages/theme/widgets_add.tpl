<form action="" method="POST">
	<div class="formelement">
		<label>Widget Type</label>
		<select name="widgetclass">
			<option value="">-- Select Widget --</option>
			{foreach from=$widget_classes item='w'}
				<option value="{$w}">{$w}</option>
			{/foreach}
		</select>
	</div>
	
	<div class="formelement">
		<label>Title</label>
		<input type="text" name="title"/>
	</div>
		
	<div class="formelement">
		<input type="submit" value="create"/>
	</div>
</form>