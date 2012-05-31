<?php


if ( ! function_exists('public_url') ) {
	function index_page() {
		$SF = &get_instance();
		//$SF->config->item('index_page');
		return 'index.php';
	}
}


if ( ! function_exists('site_url') ) {
	function site_url($site) {
		$SF = &get_instance();
		//$SF->config->site_url();
		return;
	}
}


if ( ! function_exists('base_url') ) {
	function base_url($path) {
		$SF = &get_instance();
		//$SF->config->base_url();
		return;
	}
}


if ( ! function_exists('public_url') ) {
	function public_url($path) {
		$SF = &get_instance();
		//$SF->config->public_url();
		return;
	}
}


if ( ! function_exists('current_url') ) {
	function current_url() {
		$SF = &get_instance();
		//$SF->config->site_url($SF->uri->uri_string());
		return;
	}
}


if ( ! function_exists('uri_string') ) {
	function uri_string() {
		$SF = &get_instance();
		//$SF->uri->uri_string();
		return;
	}
}


if ( ! function_exists('prep_url') ) {
	function prep_url($url = '', $scheme = 'http://') {
		if (!$url = parse_url($str) OR !isset($url['scheme'])) {
			$str = $scheme.$str;
		}
		return $str;
	}
}


if ( ! function_exists('redirect') ) {
	function redirect($uri = '', $method = 'location', $http_response_code = 302) {
		if ( ! preg_match('#^https?://#i', $uri)) {
			$uri = site_url($uri);
		}
		switch($method) {
			case 'refresh'	: header("Refresh:0;url=".$uri);
				break;
			default			: header("Location: ".$uri, TRUE, $http_response_code);
				break;
		}
		exit;
	}
}


/**
 * Parse out the attributes
 *
 * Some of the functions use this
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('_parse_attributes')) {
	function _parse_attributes($attributes, $javascript = FALSE) {
		if (is_string($attributes)) {
			return ($attributes != '') ? ' '.$attributes : '';
		}

		$att = '';
		foreach ($attributes as $key => $val) {
			if ($javascript == TRUE) {
				$att .= $key . '=' . $val . ',';
			}
			else {
				$att .= ' ' . $key . '="' . $val . '"';
			}
		}

		if ($javascript == TRUE AND $att != '') {
			$att = substr($att, 0, -1);
		}

		return $att;
	}
}