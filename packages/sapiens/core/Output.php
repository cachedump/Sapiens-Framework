<?php

class SF_Output {

	/* just if we need(we do) */
	private $config;

	/**
	 * Output-String
	 *
	 * @access 	private
	 * @var string
	 */
	private $final_output = '';

	/**
	 * The Layout-String
	 *
	 * @access 	private
	 * @var string
	 **/
	private $layout_output = '';

	/**
	 * Contains the Views applied to an specific area in Layout(used as: {$????_CONTENT$} replace ???? with the area name)
	 *
	 * @access 	private
	 * @var array
	 **/
	private $layout_areas = array();

	/**
	 * Contains the Server-Headers
	 *
	 * @access 	private
	 * @var array
	 */
	private $server_headers = array();

	/**
	 * Contains all Mime-Types
	 *
	 * @access 	private
	 * @var array
	 */
	private $mime_types	= array();

	/**
	 * Is the Page already renderen?
	 *
	 * @access 	private
	 * @var boolean
	 **/
	private $_rendered = false;
	
	function __construct() {
		global $Bootstrap;
		$this->config = &$Bootstrap->config;

		//set mime-types
		$this->mime_types = $this->config->group('Mime-Types');
	}


	/**
	 * Appends and String to the output.
	 *
	 * @access public
	 * @return void
	 **/
	public function append_output($string) {
		$this->final_output .= $string;
	}

	/**
	 * Sets the output!
	 *
	 * @access public
	 * @param string The Output to be set.
	 * @return void
	 **/
	public function set_output($output = '') {
		$this->final_output = $output;
	}

	/**
	 * Returns the current output(without layout)!
	 *
	 * @access public
	 * @return string The output.
	 **/
	public function get_output() {
		return $this->final_output;
	}

	/**
	 * Sets the Layout(override old or append to the end).
	 *
	 * @access public
	 * @param string The Layout-String
	 * @param boolean Append(true) or Override(false)?
	 * @return void
	 **/
	public function set_layout($layout = '', $append = false) {
		if ($append) {
			$this->layout_output .= $layout;
		} else {
			$this->layout_output = $layout;
		}
	}

	/**
	 * Returns the current Layout.
	 *
	 * @access public
	 * @return string
	 **/
	public function get_layout() {
		return $this->layout_output;
	}

	/**
	 * Sets the area of the Layout.
	 *
	 * @access public
	 * @param string The name ot the area
	 * @param string The content of the area
	 * @return void
	 **/
	public function set_layout_area($name, $output = '') {
		$this->layout_areas[$name] = $output;
	}

	/**
	 * Returns the Content of an Layout-Area
	 *
	 * @access public
	 * @param string The Name of the area
	 * @return string The Layout-Area-String
	 **/
	public function get_layout_area($name = false) {
		if (!$name) {
			return $this->layout_areas;
		} else {
			if (!isset($this->layout_areas[$name])) return '';
			return $this->layout_areas[$name];
		}
	}

	/**
	 * Set Content Type
	 *
	 * @access	public
	 * @param	string	can be file extension or the main mime-type('text/plain',...)
	 * @return	void
	 */
	function set_content_type($mime_type) {
		if (strpos($mime_type, '/') === FALSE) {
			$extension = ltrim($mime_type, '.');

			// Is this extension supported?
			if (isset($this->mime_types[$extension])) {
				$mime_type =& $this->mime_types[$extension];

				if (is_array($mime_type)) {
					$mime_type = current($mime_type);
				}
			}
		}

		$header = 'Content-Type: '.$mime_type;

		$this->server_headers[] = array($header, TRUE);

		return $this;
	}

	//--------------------------------------------------------------------------------------------

	/**
	 * Outputs JSON-Data to the Browser
	 * No other output after this.
	 *
	 * @access public
	 * @param mixed The Data to convert and output.
	 * @return void
	 **/
	public function json($data) {
		$this->set_output(json_encode($data));
		$this->render();
	}

	/**
	 * Outputs XML-Data to the Browser
	 * No other output after this.
	 *
	 * @access public
	 * @param mixed The Data to convert and output.
	 * @param optional string The Root-Element(default 'root')
	 * @return void
	 **/
	public function xml($data, $node_block = 'root') {
		
		function generateValidXmlFromObj(stdClass $obj, $node_block = 'root') {
	        $arr = get_object_vars($obj);
	        return generateValidXmlFromArray($arr, $node_block);
	    }
		function generateValidXmlFromArray($array, $node_block = 'root') {
	        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n";

	        if ($node_block) $xml .= '<' . $node_block . ">\r\n";
	        $xml .= generateXmlFromArray($array);
	        if ($node_block) $xml .= '</' . $node_block . ">\r\n";

	        return $xml;
	    }
	    function generateXmlFromArray($array) {
	        $xml = '';

	        if (is_array($array) || is_object($array)) {
	            foreach ($array as $key=>$value) {
	            	if (is_numeric($key)) $key = numtochars($key); 
	            	$xml .= '<' . $key . ">\r\n" . generateXmlFromArray($value, false) . '</' . $key . ">\r\n";
	            }
	        } else {
	            $xml = htmlspecialchars($array, ENT_QUOTES);
	        }

	        return $xml;
	    }

	    $xml = '';

	    if (is_object($data))
	    	$xml .= generateValidXmlFromObj($data, $node_block);
    	elseif (is_array($data))
    		$xml .= generateValidXmlFromArray($data, $node_block);
		else
			$xml .= generateValidXmlFromArray(array($data), $node_block);

		$this->set_output($xml);
		$this->render();
	}

	//--------------------------------------------------------------------------------------------

	/**
	 * Renders the Output for Browser
	 *
	 * @access public
	 * @return void
	 **/
	public function render() {
		if ($this->_rendered === true) return;
		$this->_rendered = true;

		global $BMK, $SF, $Bootstrap;

		$l_output = $this->layout_output;
		$f_output = $this->final_output;
		$l_areas = $this->layout_areas;
		$output = "";

		if (!empty($l_output)) {

			$output = $l_output;

			foreach ($l_areas as $a_name => $a_content) {
				if (strpos($output, $n = '{$'.strtoupper($a_name).'_CONTENT$}') !== FALSE) {
					$output = str_replace($n, $a_content, $output);
				}
			}

			if (strpos($output, $n = '{$PAGE_CONTENT$}') !== FALSE) {
				$output = str_replace($n, $f_output, $output);
			}

		} else {
			$output = $f_output;
		}

		$output = $Bootstrap->filter_output($output);

		$memory	 = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';

		$output = str_replace('{$elapsed_time$}', $BMK->elapsed_time('start_benchmark', 'end_benchmark'), $output);
		$output = str_replace('{$memory_usage$}', $memory, $output);
		//echo $output;

		//compress
		if ($SF->config->item('compress_output') === TRUE AND @ini_get('zlib.output_compression') == FALSE) {
			if (extension_loaded('zlib')) {
				if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
					ob_start('ob_gzhandler');
				}
			}
		}

		//send headers
		if (count($this->server_headers) > 0) {
			foreach ($this->server_headers as $header) {
				@header($header[0], $header[1]);
			}
		}

		//add cache logic here

		//END cache logic

		//send the output to the controller-method _output OR to the browser
		if (method_exists($SF, '_output')) {
			$SF->_output($output);
		} else {
			echo $output;  // Send it to the browser!
		}
	}
}