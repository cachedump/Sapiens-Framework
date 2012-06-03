<?php

if (!function_exists('lang')) {
	function lang($line, $section = false) {
		$SF = &SF_Controller::get_instance();
		return $SF->lang->line($line, $section);
	}
}