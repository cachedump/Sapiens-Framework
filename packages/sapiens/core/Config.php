<?php

class SF_Config {

	private $_configs = array('main' => array(), 'group' => array());

	private $_autoload = array('core' => array(), 'libraries' => array(), 'helper' => array(), 'models' => array(), 'language' => array());
	
	function __construct() {
		$ini = parse_ini_file(APPPATH.'config/autoload.ini', false);
		foreach ($ini as $group => $config) {
			if (isset($this->_autoload[$group]) AND !empty($config)) {
				$config = array_filter($config, function($a){
				    return is_string($a) && trim($a) !== "";
				});
				$this->_autoload[$group] = array_merge($this->_autoload[$group], $config);
			}
		}
		unset($config, $ini, $group);
		foreach (array('config') as $config) {
			$this->read_config($config, false);
		}
		unset($config);
		foreach (array('routes', 'mimes', 'paths', 'database') as $config) {
			$this->read_config($config, true);
		}
		unset($config);
	}

	public function item($item, $group = false) {
		if (!$group) {
			if (isset($this->_configs['main'][$item])) {
				return $this->_configs['main'][$item];
			}
		} else {
			if (isset($this->_configs['group'][$group][$item])) {
				return $this->_configs['group'][$group][$item];
			}
		}
		return false;
	}

	public function group($group) {
		if (isset($this->_configs['group'][$group])) {
			return $this->_configs['group'][$group];
		}
		return false;
	}

	public function add_group($name, $items = false) {
		$this->_configs['group'][$name] = array();
		if (is_array($items)) {
			foreach ($items as $item => $value) {
				$this->_configs['group'][$name][$item] = $value;
			}
		}
	}

	public function add_to_group($group, $item, $value = false) {
		if (!isset($this->_configs['group'][$group])) $this->add_group($group);
		if (is_array($item)) {
			foreach ($item as $name => $val) {
				$this->_add_config($name, $val, $group);
			}
		} else {
			$this->_add_config($item, $value, $group);
		}
	}

	public function add_config($item, $value = false) {
		if (is_array($item)) {
			foreach ($item as $key => $val) {
				$this->_add_config($key, $val);
			}
		} else {
			$this->_add_config($item, $value);
		}
	}

	private function _add_config($item, $value, $group = false) {
		if (!$group) {
			$this->_configs['main'][$item] = $value;
		} else {
			$this->_configs['group'][$group][$item] = $value;
		}
	}

	public function read_config($file, $use_groups) {
		$ini = parse_ini_file(APPPATH.'config/'.$file.'.ini', $use_groups);
		//var_dump($ini);
		if ($use_groups) {
			foreach ($ini as $groupn => $group) {
				$this->add_group($groupn, $group);
			}
		} else {
			$this->add_config($ini);
		}
	}

	public function write_config($assoc_arr, $name, $section = false) { 
	    $content = ""; 
    	$path = APPPATH.'config/'.$name.'.ini';

    	if ($section !== FALSE) {
    		$content .= "[{$section}]\r\n";
    	}

    	foreach ($assoc_arr as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if (is_int($k)) $k = '';
					$content .= "{$key}[{$k}] = {$v}\r\n";
				}
			} else {
				$content .= "{$key} = {$value}\r\n";
			}
		}

	    if (!$handle = fopen($path, 'w')) { 
	        return false; 
	    } 
	    if (!fwrite($handle, $content)) { 
	        return false; 
	    } 
	    fclose($handle); 
	    return true; 
	}

	public function get_autoloads($group = false) {
		if (!$group) return $this->_autoload;

		if (isset($this->_autoload[$group]) AND !empty($this->_autoload[$group])) {
			return $this->_autoload[$group];
		}
		return array();
	}
}