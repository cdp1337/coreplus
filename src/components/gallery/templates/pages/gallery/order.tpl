{script library="jqueryui"}{/script}
{css src="css/gallery.css"}{/css}


<form action="" method="POST">
	<input type="submit" value="Save Order"/>

	<ul id="gallery-images-sorting">
		{foreach from=$images item=i}
			<li>
				<input type="hidden" name="images[]" value="{$i.id}"/>
				{img file=$i->getPreviewFile() width="100" height="50" title="`$i.title`"}
				<span>({$i.previewsize}) {$i.title}</span>
			</li>
		{/foreach}
	</ul>

	<div class="clear"></div>

	<input type="submit" value="Save Order"/>
</form>




<script>
	$(function() {
		$( "#gallery-images-sorting" ).sortable();
		$( "#gallery-images-sorting" ).disableSelection();
	});
</script>