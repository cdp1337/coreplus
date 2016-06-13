<fieldset class="collapsible {if !$show_strings}collapsed{/if}">
	<div class="fieldset-title">
		{t 'STRING_ALL_TRANSLATION_STRINGS'}
		<i class="icon icon-chevron-down expandable-hint"></i>
		<i class="icon icon-chevron-up collapsible-hint"></i>
	</div>
	<div>
		<p class="message-tutorial">
			{t 'MESSAGE_TUTORIAL_ALL_TRANSLATION_STRINGS_AVAILABLE_CURRENTLY'}
		</p>

		{foreach $strings as $dat}
			{$dat.key}<br/>
		{/foreach}
	</div>
</fieldset>


<fieldset class="collapsible {if !$show_form}collapsed{/if}">
	<div class="fieldset-title">
		{t 'STRING_ALL_TRANSLATION_STRINGS'}
		<i class="icon icon-chevron-down expandable-hint"></i>
		<i class="icon icon-chevron-up collapsible-hint"></i>
	</div>
	<div>
		<p class="message-tutorial">
			{t 'MESSAGE_TUTORIAL_OVERRIDE_TRANSLATION_STRINGS_FOR_REQUESTED_LANGUAGE'}
		</p>

		<form method="get">
			{t 'STRING_CHANGE_LANGUAGE'}:
			<select name="lang">
				{foreach $languages as $k => $title}
					<option value="{$k}" {if $k == $requested}selected="selected"{/if}>{$title}</option>
				{/foreach}
			</select>
			<input type="submit" value="{t 'STRING_UPDATE'}"/>
		</form>

		{$form->render()}
	</div>
</fieldset>
