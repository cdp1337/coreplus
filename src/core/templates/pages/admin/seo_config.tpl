<fieldset class="collapsible collapsed">
	<legend> <i class="icon-question-circle"></i> Template Help </legend>
	<div>
		<p class="message-tutorial">
			You can use the following phrases for automatic text replacement.
		</p>
	<pre>
	%%date%%                    Replaced with the published date of the page
	%%title%%                   Replaced with the title of the page
	%%parent_title%%            Replaced with the title of the parent page of the current page
	%%sitename%%                The site's name
	%%excerpt%% 	            Replaced with the page excerpt (or auto-generated if it does not exist)
	%%tag%%                     Replaced with the current tag/tags
	%%searchphrase%%            Replaced with the current search phrase
	%%modified%%                Replaced with the page modified time
	%%name%%                    Replaced with the page author's username
	%%currenttime%%             Replaced with the current time
	%%currentdate%%             Replaced with the current date
	%%currentday%%              Replaced with the current day
	%%currentmonth%%            Replaced with the current month
	%%currentyear%%             Replaced with the current year
	<!--%%page%%                    Replaced with the current page number (i.e. page 2 of 4)
	%%pagetotal%%               Replaced with the current page total --> <!-- (future feature)  -->
	</pre>
	</div>
</fieldset>
{$form->render()}