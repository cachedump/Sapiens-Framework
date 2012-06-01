<?php

class Bootstrap {

	private $SF;

	public static $router;
	public static $output;
	public $_output;
	public $_router;
	public $config;

	function __construct() {
		global $SF, $BMK;
		$this->SF = &$SF;
		$this->BMK = &$BMK;

		$this->BMK->set_mark('finish_loading_core');
	}

	/**
	 * Initialice the System
	 *
	 * @return void
	 **/
	public function init_system() {
		//Pre System
		$this->pre_system();
		//Init Config
		$this->config = new SF_Config();
		//Init Output
		$this->_output = new SF_Output();
		self::$output = &$this->_output;
		//Pre Controller
		$this->pre_controller();
		//Init Router(routes Uri and loads Controller)
		$this->_router = new SF_Router();
		self::$router = &$this->_router;
		//Post Controller
		$this->post_controller();
		//Post System
		$this->post_system();
	}

	/**
	 * Generates the Output and send it to the browser
	 * 
	 * @return void
	 **/
	public function output() {
		//Strat outputing
		$this->_output->render();
	}

	/**
	 * Called after System is created(instanciated)!
	 *
	 * @return void
	 **/
	public function pre_system() {

	}

	/**
	 * Called before Controller is created(instanciated)!
	 *
	 * @return void
	 **/
	public function pre_controller() {

	}

	/**
	 * Called after Controller is created(instanciated)!
	 *
	 * @return void
	 **/
	public function post_controller_constructor() {
		
	}

	/**
	 * Called after Controller is executed!
	 *
	 * @return void
	 **/
	public function post_controller() {
		
	}

	/**
	 * Called after System is executed!
	 *
	 * @return void
	 **/
	public function post_system() {
		
	}

	/**
	 * Called before Outbut is sent!
	 * For Filtering, ...
	 *
	 * @param string The Output, going to be sent to the browser.
	 * @return string The Output, sent to the browser.
	 **/
	public function filter_output($output = '') {
		return $output;
	}

	public static function show_error($msg) {
		die("<h1>An error occoruped!</h1><p>$msg</p>");
	}
	public static function show_404() {
		if (!empty(self::$router->_routes['404_override'])) {
			include APPPATH.'views/'.self::$router->_routes['404_override'].'.php';
		} else {
			die("<h1>An error occoruped!</h1><p>Controller was not found.</p>");
		}
	}

}

if ( ! function_exists('show_error') ) {
	function show_error($msg) {
		return Bootstrap::show_error($msg);
	}
}

if ( ! function_exists('show_404') ) {
	function show_404() {
		return Bootstrap::show_404();
	}
}

if ( ! function_exists('log_message') ) {
	function log_message($status, $message) {
		
		$file = APPPATH.'log/'.date('m-d-Y').'_log.txt';
		Log::write($file, $message);
	}
}