<?php

//Benchmark
require COREPATH . 'core/Benchmark.php';
$BMK = new SF_Benchmark();

//Needs to be loaded for the inner core
require COREPATH . 'core/Bootstrap.php';
require COREPATH . 'core/Log.php';
require COREPATH . 'core/Router.php';
require COREPATH . 'core/Config.php';
require COREPATH . 'core/Loader.php';
require COREPATH . 'core/Output.php';
require COREPATH . 'core/Input.php';
require COREPATH . 'core/Language.php';
require COREPATH . 'core/Uri.php';

//Needs to be loaded for Controllers ans Models
require COREPATH . 'core/Controller.php';
require COREPATH . 'core/Model.php';

$SF;
$Bootstrap = new Bootstrap();

//Init System
$Bootstrap->init_system();
$Bootstrap->output();

//For Debuging
//var_dump($Bootstrap);
