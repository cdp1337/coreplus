# {insertable} Smarty Block

Insertables are the core method of injecting blocks of user-customizable content into a template.

An insertable must be on a template that has a registered page URL, as the baseurl is what is tracked as one of the main primary keys.
The other PK is the insertable's name, which must be unique on that one template.

## Parameters

* name
	* The key name of this input value, must be present and unique on this template.
* assign
	* Assign the value instead of outputting to the screen.
* title
	* When editing the insertable, the title displayed along side the input field.
* type

## Example Usage

	{insertable name="body" title="Body Content"}
		<p>
			This is some example content!
		</p>
	{/insertable}

	{insertable name="img1" title="Large Image" assign="img1"}
		{img src="`$img1`" placeholder="generic" dimensions="800x400"}
	{/insertable}