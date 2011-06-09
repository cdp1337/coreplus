<?php

class Debug{
	
	public static function Write($text){
		if(!FULL_DEBUG) return;
		/*
		$out = '';
		foreach($argv as $arg){
			if($arg typeof 'Array') $out .= '<span class="cae2_debug_array">Array ' . 
		}*/
		if(EXEC_MODE == 'CLI') echo '[ DEBUG ] - ' . $text . "\n";
		else echo "<div class='cae2_debug'>" . $text . "</div>"; 
	}
}
