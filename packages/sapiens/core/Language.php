<?php

/**
 * @TODO Write Language-Class and test it
*/

class SF_Language {

	private $SF;

	/**
	 * Name of the language
	 *
	 * @var string
	 **/
	private $_lang = '';

	/**
	 * Contains the content of all language-files
	 *
	 * @var array
	 **/
	private $_lang_lines = array();
	
	function __construct() {
		$this->SF = &SF_Controller::get_instance();
		$this->_lang = $this->SF->config->item('language');
	}

	/**
	 * Returns the translated string or the original string.
	 *
	 * @param string the key
	 * @param optional string the section to search in
	 * @return void
	 **/
	public function line($key, $section = false) {
		if (empty($this->_lang_lines)) return $key;
		if ($section) {
			//search in section
			if (isset($this->_lang_lines[$section], $this->_lang_lines[$section][$key])) return $this->_lang_lines[$section][$key];
		} else {
			//search in every section
			foreach ($this->_lang_lines as $sect => $keys) {
				if (isset($keys[$key])) {
					return $keys[$key];
				}
			}
		}
		//nothing found!
		return $key;
	}

	/**
	 * Adds a section to the array overrides another on same name
	 *
	 * @param string Name of the section
	 * @param optional array Lines be added to the section
	 * @return void
	 **/
	public function add_section($section, $lines = array()) {
		$this->_lang_lines[$section] = $lines;
	}

	/* @TODO add change_section, delete_section, add_to_section and get_section functions */

}