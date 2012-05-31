<?php

/* @TODO besseren XSS-Filter einbauen */

class SF_Input {

	/**
	 * Contains the Posted-Data of the $_POST
	 *
	 * @var array
	 **/
	private $_post_data = array();

	/**
	 * Contains the Posted-Data of the $_GET
	 *
	 * @var array
	 **/
	private $_get_data = array();

	/**
	 * Contains the Cookie-Data of the $_COOKIE
	 *
	 * @var array
	 **/
	private $_cookie_data = array();

	/**
	 * Contains the IP-Adress of the user-client
	 *
	 * @var mixed
	 **/
	private $_ip_adress = false;

	/**
	 * Contains the user-agent of the user-client
	 *
	 * @var mixed
	 **/
	private $_user_agent = false;

	/**
	 * Contains the headers sent to server
	 *
	 * @var mixed
	 **/
	private $_headers = false;

	/**
	 * On true filters for XSS
	 *
	 * @var boolean
	 **/
	private $_enable_xss = true;
	
	function __construct() {
		$this->_post_data = $_POST;
		$this->_get_data = $_GET;
		$this->_cookie_data = $_COOKIE;

		//add config-item : unset or not
		//$_POST = array();
		//$_GET = array();
		//$_COOKIE = array();

		if ($this->_enable_xss) {
			$this->_post_data = $this->xss_clean($this->_post_data);
			$this->_get_data = $this->xss_clean($this->_get_data);
		}
	}

	//--------------------------------------------------------

	/**
	 * Returns the post-data of the field(false if not there) or all post-data
	 *
	 * @param optional string The name of the post-field
	 * @return mixed
	 **/
	public function post($field = false) {
		if ($field) {
			if (!isset($_POST[$field]))
				return false;
			else
				return $_POST[$field];
		} else {
			return $_POST;
		}
	}

	/**
	 * Returns the get-data of the field(false if not there) or all get-data
	 *
	 * @param optional string The name of the post-field
	 * @return mixed
	 **/
	public function get($field = false) {
		if ($field) {
			if (!isset($_GET[$field]))
				return false;
			else
				return $_GET[$field];
		} else {
			return $_GET;
		}
	}

	/**
	 * Returns the cookie-data of the field(false if not there) or all cookie-data
	 *
	 * @param optional string The name of the cookie-field
	 * @return mixed
	 **/
	public function cookie($field = false) {
		if ($field) {
			if (!isset($_COOKIE[$field]))
				return false;
			else
				return $_COOKIE[$field];
		} else {
			return $_COOKIE;
		}
	}

	/**
	 * Returns the server-data of the field(false if not there) or all server-data
	 *
	 * @param optional string The name of the server-field
	 * @return mixed
	 **/
	public function server($field = false) {
		if ($field) {
			if (!isset($_SERVER[$field]))
				return false;
			else
				return $_SERVER[$field];
		} else {
			return $_SERVER;
		}
	}

	/**
	 * Returns the IP-Adress of the user-client
	 *
	 * @return string
	 **/
	public function ip_adress() {
		if ($this->_ip_adress !== FALSE) {
			return $this->_ip_adress;
		}

		if ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP')) {
			$this->_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ($this->server('REMOTE_ADDR')) {
			$this->_ip_adress = $_SERVER['REMOTE_ADDR'];
		} elseif ($this->server('HTTP_CLIENT_IP')) {
			$this->_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ($this->server('HTTP_X_FORWARDED_FOR')) {
			$this->_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($this->_ip_adress === FALSE) {
			$this->_ip_adress = '0.0.0.0';
			return $this->_ip_adress;
		}

		if (strpos($this->_ip_adress, ',') !== FALSE) {
			$x = explode(',', $this->_ip_adress);
			$this->_ip_adress = trim(end($x));
		}

		if ( ! $this->valid_ip($this->_ip_adress)) {
			$this->_ip_adress = '0.0.0.0';
		}

		return $this->_ip_adress;
	}

	/**
	 * Check if the specified IP-Adress is valid
	 *
	 * @param string The IP-Adress to be checked
	 * @return boolean
	 **/
	public function valid_ip($ip) {
		$parts = explode('.', $ip);

		// always 4 parts needed
		if (count($parts) != 4) {
			return FALSE;
		}
		// IP can not start with 0
		if ($parts[0][0] == '0') {
			return FALSE;
		}
		// check each part
		foreach ($parts as $part) {
			// IP parts must be digits and can not be
			// longer than 3 digits or greater then 255
			if ($part == '' OR preg_match("/[^0-9]/", $part) OR strlen($part) > 3 OR $part > 255) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author 
	 **/
	public function user_agent() {
		if ($this->_user_agent !== FALSE) {
			return $this->user_agent;
		}

		$this->_user_agent = ( ! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];

		return $this->_user_agent;
	}

	/**
	 * Filters an string/array and return the clean string
	 *
	 * @param string/array
	 * @return mixed
	 **/
	public function xss_clean($data) {
		if (!is_array($data)) {
			return htmlentities($data);
		}
		foreach ($data as $key => $value) {
			$data[$key] = htmlentities($value);
		}
		return $data;
	}

	/**
	 * Returns all headers and saves them in an private-array
	 *
	 * @return array
	 **/
	public function request_headers() {
		// if apache-server running use this function
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
		} else {
			$headers['Content-Type'] = (isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

			foreach ($_SERVER as $key => $val) {
				if (strncmp($key, 'HTTP_', 5) === 0) {
					$headers[substr($key, 5)] = $this->_fetch_from_array($_SERVER, $key, $xss_clean);
				}
			}
		}

		// replace '_' with '-' and Humanize the words
		foreach ($headers as $key => $val)
		{
			$key = str_replace('_', ' ', strtolower($key));
			$key = str_replace(' ', '-', ucwords($key));

			$this->_headers[$key] = $val;
		}

		return $this->_headers;
	}

	/**
	 * Returns an Header
	 *
	 * @return string
	 **/
	public function get_request_header($key) {
		if (empty($this->_headers)) {
			$this->request_headers();
		}

		if ( ! isset($this->_headers[$key])) {
			return FALSE;
		}

		return $this->_headers[$key];
	}

	/**
	 * Checks if this rerquest is an AJAX-request
	 *
	 * @return boolean
	 **/
	public function is_ajax_request() {
		return ($this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
	}

	/**
	 * Checks if the request was wade from the command line
	 *
	 * @return boolean
	 **/
	public function is_cli_request() {
		return (php_sapi_name() == 'cli') or defined('STDIN');
	}

	/**
	* Sets an Cookie
	*
	* @access	public
	* @param	mixed
	* @param	string	value
	* @param	string	expiration(seconds)
	* @param	string	domain
	* @param	string	path
	* @param	string	prefix
	* @return	void
	*/
	public function set_cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = FALSE) {
		//add config for each if-statemant
		/*if ($prefix == '') {
			$prefix = $prefix;//config-item here
		}
		if ($domain == '') {
			$domain = $domain;//config-item here
		}
		if ($path == '/') {
			$path = $path;//config-item here
		}
		if ($secure == FALSE) {
			$secure = $secure;//config-item here
		}

		if ( ! is_numeric($expire)) {
			$expire = time() - 86500;
		} else {
			$expire = ($expire > 0) ? time() + $expire : 0;
		}*/

		setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
	}

}