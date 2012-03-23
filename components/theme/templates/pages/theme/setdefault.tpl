{**
 * Basic template that is displayed for the set default option when it's of method GET.
 * This is because the request must be POST in order for it to do anything.
 *}

<p class="info">Set {$theme}/{$template} as default?</p>

 <form action="" method="POST">
	 <input type="submit" value="Set Default"/>
 </form>