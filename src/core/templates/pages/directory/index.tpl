{foreach from=$files item='file'}
	{file_thumbnail file=$file size='lg'}
	{$file->getBaseFilename()}<br/>
{/foreach}