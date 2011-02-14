<!-- Begin rendering of radio(s) Form element {$element->arguments.name} -->
<table 
  class="{$element->arguments.class}
  {if $element->getArgument('required')} FormRequired{/if}
  {if $element->hasError()} FormError{/if}"
>
  <tr>
    {if $element->title ne ''}
      <td class="label"><label for="{$element->arguments.name}">{$element->title|escape}</label></td>
    {else}
      <td></td>
    {/if}
    
    <td class="input">
    
      {if is_array($element->value)}
        {foreach from=$element->value item=val}
          <div class="radioOption">
            <input type="radio" {$element->getArgumentsAsString()} value="{$val->value|escape}"{if $val->checked} checked{/if}>
            <span>{$val->title}</span>
          </div>
        {/foreach}
      {else}
        <input type="radio" {$element->getArgumentsAsString()} value="{$element->value|escape}">
      {/if}
    </td>
  </tr>
  
  {if $element->description ne ''}
    <tr class="FormDescription"><td colspan="2">{$element->description}</td></tr>
  {/if}
</table>
<!-- End rendering of Form element {$element->arguments.name} -->

