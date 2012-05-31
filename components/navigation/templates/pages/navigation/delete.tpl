<h2>Confirm Delete?</h2>

<p>
	Are you sure you want to completely delete the following page:
</p>
<ul>
	<li>{$model->get('title')}</li>
	<li>{$model->getLink('Page')->get('rewriteurl')}</li>
</ul>

<p>
<div style = "float:left; width:50%; text-align:center;">{a href=$model->getLink('Page')->get('rewriteurl') title="No" class="button"}
	No!{/a}</div>
<div style = "text-align:center;">{a href="/Content/Delete/`$model->get('id')`/confirm" class="button delete"}Yes,
	Delete!{/a}</div>
</p>
