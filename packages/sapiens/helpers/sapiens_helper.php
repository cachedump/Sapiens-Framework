<?php

if (!function_exists('numtochars')) {
	function numtochars($num, $start = 65, $end = 90) {
	    $sig = ($num < 0);
	    $num = abs($num + 1);
	    $str = "";
	    $cache = ($end - $start);
	    while($num != 0) {
	        $str = chr(($num % $cache) + $start - 1).$str;
	        $num = ($num - ($num % $cache)) / $cache;
	    }
	    if($sig) {
	        $str = "-".$str;
	    }
	    return $str;
	}
}