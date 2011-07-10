{foreach from=$files item='file'}
	{file_thumbnail file=$file}
	{$file->getBaseFilename()}<br/>
{/foreach}