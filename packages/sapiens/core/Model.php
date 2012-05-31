<?php

class SF_Model {

	function __construct() {
		//echo "Model-parrent! ";
	}

	function __get($key) {
		$SF = &SF_Controller::get_instance();
		return $SF->$key;
	}

}