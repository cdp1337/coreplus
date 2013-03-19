{script library="jqueryui"}{/script}
{script src="js/core.fileupload.js"}{/script}

<a class="button update-link" title="Upload New Image">
	<i class="icon-upload"></i>
	<span>Upload New Image</span>
</a>
<br/><br/>


<ul>

	{foreach from=$images item=i}
		<li>
			<div class="gallery-admin-image-wrapper">
				<div class="gallery-admin-image">
					{img src="public/galleryalbum/`$i.file`" width="200" height="200" title="`$i.title`"}
				</div>


			</div>
		</li>
	{/foreach}

</ul>



