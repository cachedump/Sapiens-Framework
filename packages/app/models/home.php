<?php

class Home_Model extends SF_Model {

	function __construct($config = array()) {
		parent::__construct();
		echo "Constructed!! Jey! ";
	}

	public function test() {
		echo $this->config->item('language');
	}
}