<?php

class SF_Benchmark {

	private $_markes = array();
	
	private $_finish_time;
	private $_memory_usage;
	
	function __construct() {
		$this->_markes['start_benchmark'] = microtime();
		$this->_markes['start_loading_core'] = microtime();
	}

	/**
	 * Set a Mark-Point(safes time)
	 *
	 * @param string The Name of the mark.
	 * @return void
	 **/
	public function set_mark($mark) {
		$this->_markes[$mark] = microtime();
	}

	/**
	 * Returns the timespan between two marks.
	 *
	 * @param string Name of the first Mark
	 * @param string Name of the second Mark
	 * @return string formated number
	 **/
	public function elapsed_time($mark1 = false, $mark2 = false) {
		//var_dump($this->_markes);
		if (!$mark1 AND !$mark2) {
			return '{%elapsed_time%}';
		}

		if (!isset($this->_markes[$mark1])) {
			return FALSE;
		}

		if (!isset($this->_markes[$mark2])) {
			$this->_markes[$mark2] = microtime();
		}

		list($sm, $ss) = explode(' ', $this->_markes[$mark1]);
		list($em, $es) = explode(' ', $this->_markes[$mark2]);

		return number_format(($em + $es) - ($sm + $ss), 4);
	}

}