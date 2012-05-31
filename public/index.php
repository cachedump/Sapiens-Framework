<?php

//turn error-reporting on
error_reporting(E_ALL);

//Define a few const.
defined('BASEPATH') or define('BASEPATH', dirname(realpath(__FILE__)) . '/../');
defined('PKGPATH') or define('PKGPATH', BASEPATH . 'packages/');
defined('COREPATH') or define('COREPATH', PKGPATH . 'sapiens/');
defined('APPPATH') or define('APPPATH', PKGPATH . 'app/');

defined('ENVOIREMENT') or define('ENVOIREMENT', 'debug');

//Loads the Base-File
require COREPATH . 'Sapiens.php';


/*
 * @todo load config.ini(no groups)
 * @todo Make Language-Class based on the zend-translate-class (for gettext)
*/
