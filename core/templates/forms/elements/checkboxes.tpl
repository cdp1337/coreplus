<!-- Begin rendering of checkboxes Form element {$element->arguments.name} -->
<table 
  class="{$element->arguments.class}
  {if $element->getArgument('required')} FormRequired{/if}
  {if $element->hasError()} FormError{/if}"
>
  <tr>
    {if $element->title ne ''}
      <td><label for="{$element->arguments.name}">{$element->title|escape}</label></td>
    {else}
      <td></td>
    {/if}
    
    {if is_array($element->value)}
      {foreach from=$element->value item=val}
        <div>
          <label>{$val->title}</label>
          <input type="checkbox" {$element->getArgumentsAsString()} value="{$val->value|escape}">
        </div>
      {/foreach}
    {else}
      <input type="checkbox" {$element->getArgumentsAsString()} value="{$element->value|escape}">
    {/if}
  
  {if $element->description ne ''}
    <div class="FormDescription">
      {$element->description}
    </div>
  {/if}
</table>
<!-- End rendering of Form element {$element->arguments.name} -->
