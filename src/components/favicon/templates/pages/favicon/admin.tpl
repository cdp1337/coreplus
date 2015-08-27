{css}<style>
.preview-tile {
	float: left;
	margin: 0.25em;
	text-align: center;
}
</style>{/css}

<p class="message-tutorial">
	The favicon is an image that is used heavily alongside your site's identity.
	It is displayed in the tab bar of browsers, on bookmark links, and on mobile devices.
	<br/><br/>
	An ideal image to use is a square PNG or JPG at least 512x512 pixels in size.
</p>

{if $current}
	<fieldset class="collapsible collapsed">
		<div class="fieldset-title">
			How does this look on different devices?
			<i class="icon-chevron-down expandable-hint"></i><i class="icon-chevron-up collapsible-hint"></i>
		</div>
		<div class="preview-tile">
			i[Pad/Phone] with Retina Display<br/>
			{img src="$current" dimensions="512x512!"}
		</div>

		<div class="preview-tile">
			Windows 8 Metro<br/>
			{img src="$current" dimensions="270x270!"}
		</div>

		<div class="preview-tile">
			i[Pad/Phone] 2nd Gen<br/>
			{img src="$current" dimensions="114x114!"}
		</div>

		<div class="preview-tile">
			i[Pad/Phone] 1st Gen<br/>
			{img src="$current" dimensions="72x72!"}
		</div>

		<div class="preview-tile">
			Web<br/>
			{img src="$current" dimensions="32x32!"}
		</div>
	</fieldset>

{/if}

{$form->render()}