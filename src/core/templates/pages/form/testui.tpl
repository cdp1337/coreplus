<p class="message-tutorial">
	This page displays every registered from element on the system along with several basic styling options to select from.
	You can use this page to preview styles on your site, particularly useful for frontend developers.
	<br/><br/>
	You are currently viewing the {$orientation} orientation.
</p>
<form action="" method="GET">
	<select name="orientation">
		<option value="horizontal" {if $orientation == 'horizontal'}selected="selected"{/if}>horizontal</option>
		<option value="vertical" {if $orientation == 'vertical'}selected="selected"{/if}>vertical</option>
		<option value="grid" {if $orientation == 'grid'}selected="selected"{/if}>grid</option>
		<!--<option value="css-test" {if $orientation == 'css-test'}selected="selected"{/if}>css-test</option>-->
	</select>

	<label>Mark Required <input type="checkbox" name="required" {if $required}checked="checked"{/if}/></label>
	<label>Mark Error <input type="checkbox" name="error" {if $error}checked="checked"{/if}/></label>

	<input type="submit" value="Set"/>
</form>
<hr/>

{$form->render()}