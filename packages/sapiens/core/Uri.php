<?php

class SF_Uri {

	private $SF;

	/**
	 * The URI-String
	 *
	 * @var string
	 **/
	private $_uri_string;

	/**
	 * An array of segments
	 *
	 * @var array
	 **/
	private $_segments = array();

	/**
	 * An array of routed segments
	 *
	 * @var array
	 **/
	private $_rsegments;

	function __construct() {
		$this->SF = &get_instance();

		$this->_uri_string = $this->_detect_uri();
		$this->_segments = explode('/', $this->_uri_string);
		if (empty($this->_segments[0])) {
			$this->_segments = array();
		}
	}

	public function uri_string() {
		return $this->_uri_string;
	}

	public function segments() {
		return $this->_segments;
	}

	public function rsegments() {
		return $this->_segments;
	}

	public function segment($index) {
		if (!isset($this->_segments[$index])) return NULL;
		return $this->_segments[$index];
	}

	public function rsegment($index) {
		if (!isset($this->_rsegments[$index])) return NULL;
		return $this->_rsegments[$index];
	}

	public function site_url($site) {
		return '/'.$this->_index_page;
	}



	public function set_routed($routed) {
		if (is_array($routed))
			$this->_rsegments = $routed;
		else
			$this->_rsegments = $this->_segments;
	}

	//------------------------------------------------------------------------------

	private function _detect_uri() {
		if (!isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) return false;

		$uri = $_SERVER['REQUEST_URI'];

		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		} elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		//split up the query string
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];

		//cleans up the qury string and the $_GET
		if (isset($parts[1])) {
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		} else {
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}

		//detect empty url
		if ($uri == '/' OR empty($uri)) {
			return '/';
		}
		
		//clean the url and return it
		$uri = parse_url($uri, PHP_URL_PATH);
		return str_replace(array('../', '//'), '/', trim($uri, '/'));
	}

	
}