{$listing->render('head')}

<tr>
	<td colspan="3">
		<div class="view">
			Spam Score Threshold: {$threshold}

			<a href="#" class="control-edit-toggle"><i class="icon icon-pencil"></i></a>
		</div>
		<div class="edit">
		{add_form_element
			form=$listing
			name="threshold"
			type="text"
			size="3"
			title="Spam Score Threshold"
			description="The total score before content is marked as spam."
			value="`$threshold`"}
		</div>
	</td>
</tr>

{foreach $listing as $row}
	<tr>
		<td>
			{$row.keyword}
		</td>
		<td>
			<span class="view">
				<a href="#" class="control-edit-toggle">{$row.score}</a>
			</span>
			<div class="edit">
				{add_form_element form=$listing name="score[`$row.keyword`]" type="text" size="3" value="`$row.score`"}
			</div>
		</td>
		<td></td>
	</tr>
{/foreach}

<tr>
	<td>
		<div class="view">
			<a href="#" class="control-edit-toggle"><i class="icon icon-add"></i> Add New Keyword</a>
		</div>
		<div class="edit">
			{add_form_element
				form=$listing
				placeholder="Keyword"
				name="new_keyword"
				type="text"
				size="20"
				value=""}
		</div>
	</td>
	<td>
		<div class="view">

		</div>
		<div class="edit">
			{add_form_element
				form=$listing
				placeholder="#"
				name="new_score"
				type="text"
				size="3"
				value=""}
		</div>
	</td>
	<td></td>
</tr>

{$listing->render('foot')}