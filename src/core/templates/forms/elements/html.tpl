<!-- Begin rendering of Form element {$element->arguments.name} -->
<table 
  class="{$element->arguments.class}
  {if $element->getArgument('required')} FormRequired{/if}
  {if $element->hasError()} FormError{/if}"
>
  <tr>
    {if $element->title ne ''}
      <td class="label"><label for="{$element->arguments.name}">{$element->title|escape}</label></td>
    {/if}
    <td class="input">{$element->value}</td>
  </tr>
  {if $element->description ne ''}
    <tr class="formdescription"><td colspan="2">{$element->description}</td></tr>
  {/if}
</table>
<!-- End rendering of Form element {$element->arguments.name} -->
