<?php

echo "Helper loaded.";

if (!function_exists('test')) {
	function test() {
		echo "Worked!";
	}
}