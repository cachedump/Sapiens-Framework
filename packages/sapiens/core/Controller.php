<?php

class SF_Controller {
	
	private static $instance;

	function __construct() {
		self::$instance = &$this;

		global $Bootstrap;
		//assign the Config-Class from global Bootstrap
		$this->config = &$Bootstrap->config;
		//assign the Output-Class from global Bootstrap
		$this->output = &$Bootstrap->_output;
		//init the Loader-Class
		$this->load = new SF_Loader();
		//init the Input-Class
		$this->input = new SF_Input();
		//init the Language-Class
		$this->lang = new SF_Language();
		//assign the Uri-Class from global Bootstrap
		$this->uri = &$Bootstrap->_uri;
	}

	/**
	 * Returns the Reference to this object.
	 *
	 * @access public
	 * @static
	 * @return object
	 **/
	public static function &get_instance() {
		return self::$instance;
	}

}

if (!function_exists('get_instance')) {
	function &get_instance() {
		return SF_Controller::get_instance();
	}
}