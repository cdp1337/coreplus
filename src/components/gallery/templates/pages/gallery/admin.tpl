<table class="listing">
	<tr>
		<th>Nickname</th>
		<th>Link</th>
		<th>Images</th>
		<th>Videos</th>
		<th>Audios</th>
		<th>Files</th>
		<th width="80">&nbsp;</th>
	</tr>
{foreach from=$albums item=album}
	<tr>
		<td>{$album->get('title')}</td>
		<td>
			{a href="/gallery/view/`$album->get('id')`"}
				{link link="/gallery/view/`$album->get('id')`"}
			{/a}
		</td>
		<td>
			{$album->getChildrenCount('image')}
		</td>
		<td>
			{$album->getChildrenCount('video')}
		</td>
		<td>
			{$album->getChildrenCount('audio')}
		</td>
		<td>
			{$album->getChildrenCount('file')}
		</td>
		<td>
			<ul class="controls">
				<li class="view">
					{a href="/gallery/view/`$album->get('id')`" title="View"}
						<i class="icon-eye-open"></i><span>View</span>
					{/a}
				</li>
				<li class="edit">
					{a href="/gallery/edit/`$album->get('id')`" title="Edit"}
						<i class="icon-edit"></i><span>Edit</span>
					{/a}
				</li>
				<li class="delete">
					{a href="/gallery/delete/`$album->get('id')`" title="Delete" confirm="Are you sure you want to delete `$album->get('title')`?"}
						<i class="icon-remove"></i><span>Delete</span>
					{/a}
				</li>
			</ul>
		</td>
	</tr>
{/foreach}
</table>