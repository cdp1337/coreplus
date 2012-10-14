{$form->render('head')}
{$form->render('body')}

{if $article.fb_post_id}
	<p class="message-info">This article has already been posted to facebook!</p>
{/if}

<input type="submit" value="Update Article"/>

{$form->render('foot')}