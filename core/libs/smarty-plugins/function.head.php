<?php

function smarty_function_head($params, $template){
	
	// Load any head elements currently in the CurrentPage cache
	return CurrentPage::GetHead();
}
