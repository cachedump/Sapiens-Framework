<?php

class SF_Router {

	protected $_routes = array();

	private $_controller = '';
	private $_class = '';
	private $_method = 'index';
	private $_args = array();

	private $uri_string = '';

	function __construct() {
		global $Bootstrap, $SF;
		//take uri string
		$url = $this->uri_string = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : NULL;
		//loads routes
		$this->_routes = $Bootstrap->config->group('Routes');

		$url = ltrim($url, '/');
		$url = filter_var($url, FILTER_SANITIZE_URL);
		$url = explode('/', $url);

		//check for routes to be used
		$routes = $this->_routes;
		unset($routes['default_controller'], $routes['404_override']);
		foreach ($routes as $uri => $route) {
			$urla = explode('/', $uri);
			$a = -1; $b = false;$c = array();

			foreach ($urla as $urlb) {
				$a++;
				if (isset($url[$a]) AND $urlb == $url[$a]) {
					continue;
				} elseif ($urlb == '%any%' AND isset($url[$a]) AND !empty($url[$a])) {
					$c[] = $url[$a];
					continue;
				} elseif ($urlb == '%num%' AND isset($url[$a]) AND !empty($url[$a]) AND is_numeric($url[$a])) {
					$c[] = $url[$a];
					continue;
				} else {
					$b = true;
					break;
				}
				
			}

			if ($b === false) {
				$url = explode('/',$route);
			}

			if (!empty($c)) {
				$e = 0;
				foreach ($url as $u) {
					if ($u[0] == '$' AND isset($c[$d = (int)substr($u, 1) - 1])) {
						$url[$e] = $c[$d];
					}
					$e++;
				}
			}

			unset($a, $b, $c, $d, $e, $routes);
		}
		
		if (empty($url[0])) {
			$url[0] = $this->_routes['default_controller'];
		}

		$file = APPPATH.'controllers/' . $url[0] . '.php';
		if (file_exists($file)) {
			require $file;
		} else {
			Bootstrap::show_404();
		}

		//init Conroller
		$SF = new $url[0]();

		//post Controller-Costructor
		$Bootstrap->post_controller_constructor();

		// calling methods
		if (isset($url[2])) {
			if (method_exists($SF, $url[1])) {
				call_user_func_array(array($SF, $url[1]), array_slice($url, 2));
			} else {
				$this->error();
			}
		} else {
			if (isset($url[1])) {
				if (method_exists($SF, $url[1])) {
					$SF->{$url[1]}();
				} else {
					Bootstrap::show_404();
				}
			} else {
				$SF->index();
			}
		}
	}

	public function uri_string() {
		return $this->uri_string;
	}
	

}