<?php

class SF_Loader {

    private $instance;

    private $_view_paths = array();
    private $_layout_paths = array();
    private $_lib_paths = array();
    private $_model_paths = array();
    private $_core_paths = array();
    private $_language_paths = array();
    private $_helper_paths = array();
    private $_config_paths = array();

    private $_loaded_files = array();
    private $_loaded_libs = array();
    private $_loaded_models = array();
    private $_loaded_cores = array();
    //private $_loaded_languages = array();
    private $_loaded_helpers = array();

    function __construct() {
        $this->instance = &SF_Controller::get_instance();
        //load paths
        $this->_view_paths = $this->instance->config->item('view', 'Paths');
        $this->_layout_paths = $this->instance->config->item('layout', 'Paths');
        $this->_lib_paths = $this->instance->config->item('lib', 'Paths');
        $this->_model_paths = $this->instance->config->item('model', 'Paths');
        $this->_core_paths = $this->instance->config->item('core', 'Paths');
        $this->_language_paths = $this->instance->config->item('language', 'Paths');
        $this->_helper_paths = $this->instance->config->item('helper', 'Paths');
        $this->_config_paths = $this->instance->config->item('config', 'Paths');
        //var_dump($this);
    }

    /**
     * Loads the specified view. And appends it to the output OR returns it.
     *
     * @access public
     * @param string The name/path of the view(without 'views/' and '.php')
     * @param optional array The Variables to be used in the view. Are going to be extracted
     * @param optional string The name of the area in with this view should been placed(if false place in 'PAGE_CONTENT') ignored on return
     * @param optional boolean Do you want to return the data? Default: false
     * @return void/string
     **/
    public function view($view, $vars = array(), $area = false, $return = false) {
        foreach ($this->_view_paths as $view_path) {
            if (file_exists($full_path = $view_path.$view.'.php')) {
                ob_start();
                extract($vars);
                include $full_path;
                $view_content = ob_get_contents();
                ob_end_clean();
                if ($return) {
                    return $view_content;
                } else {
                    if (!$area) {
                        $this->instance->output->append_output($view_content);
                    } else {
                        $this->instance->output->set_layout_area($area, $view_content);
                    }
                }
            }
        }
    }

    /**
     * Loads an layout for the View(s)
     *
     * @param string The name/path of the layout(without 'layouts/' and '.php')
     * @param optional array The Variables to be used in the layout. Are going to be extracted
     * @param optional boolean Do you want to return the data? Default: false
     * @return void
     **/
    public function layout($layout, $vars = array(), $append = true, $return = false) {
        foreach ($this->_layout_paths as $layout_path) {
            if (file_exists($full_path = $layout_path.$layout.'.php')) {
                ob_start();
                extract($vars);
                include $full_path;
                $layout_content = ob_get_contents();
                ob_end_clean();
                if ($return) {
                    return $layout_content;
                } else {
                    $this->instance->output->set_layout($layout_content, $append);
                }
            }
        }
    }

    /**
     * Loads a Library and creates an Object to call it in the Controller/Model
     *
     * @access public
     * @param string The class(withpath, no 'libraries/' and '.php')
     * @param optional array The config-array passed to the library overrides additional config-file
     * @param optional string Without this the class willbe callable under the lower Classname(in Controller)
     * @return void
     **/
    public function library($class, $config = NULL, $alias = false) {
        if (is_array($class)) {
            foreach ($class as $lib) {
                $this->library($lib, $config);
            }
        }

        $class = trim($class);
        $subdir = '';
        if ($last_slash = strpos($class, '/') !== FALSE) {
            $subdir = substr($class, 0, $last_slash + 1);
            $class = substr($class, $last_slash + 1);
        }

        if (!$alias) $alias = strtolower($class);

        if (in_array(strtolower($subdir.$class), $this->_loaded_libs)) {
            //already loaded : create a copy of the instance
            log_message('debug', "create copy of library(already loaded)");
            $clone = array_search(strtolower($subdir.$class), $this->_loaded_libs);
            if (isset($this->instance->$alias)) {
                //alias-token : error
                log_message('debug', "Error: Alias already token!");
                return;
            }
            $this->instance->$alias = clone $this->instance->$clone;
            return;
        }

        if (isset($this->instance->$alias)) {
            //alias-token : error
            log_message('debug', "Error: Alias already token!");
            return;
        }

        //insert logic for class extending

        foreach (array_merge($this->_lib_paths, $this->_core_paths) as $path) {
            //for linux and mac
            if (file_exists($end_path = $path.$subdir.strtolower(ucfirst($class)).'.php')) {
                log_message('debug', "found(ucfirst version)");

                $this->_library($class, $subdir, $end_path, $config, $alias);
                return;
            }

            if (file_exists($end_path = $path.$subdir.strtolower($class).'.php')) {
                log_message('debug', "found(lower version)");

                $this->_library($class, $subdir, $end_path, $config, $alias);
                return;
            }

            //check if classname is path
            if (empty($subdir)) {
                //for linux and mac
                if (file_exists($end_path = $path.$class.'/'.strtolower(ucfirst($class)).'.php')) {
                    log_message('debug', "found(ucfirst version) in subfolder like clasname");

                    $this->_library($class, $class, $end_path, $config, $alias);
                    return;
                }

                if (file_exists($end_path = $path.$class.'/'.strtolower($class).'.php')) {
                    log_message('debug', "found(lower version) in subfolder like clasname");

                    $this->_library($class, $class, $end_path, $config, $alias);
                    return;
                }
            }
        }

        //no file found
        echo "Library \"$class\" cought not be found!";
    }

    /**
     * Loads a Model and creates an Object to call it in the Controller/Model
     *
     * @param string Name of the model(path) no 'model/' or '.php'
     * @param optional arrayConfig Items to be parsed to the Model-Constructor
     * @param optional string Name of the Variable assign it to
     * @return void
     **/
    public function model($model, $config = NULL, $alias = false) {
        if (is_array($model)) {
            foreach ($model as $mod) {
                $this->model($mod, $config);
            }
        }

        $model = trim($model, '/');
        $subdir = '';
        if ($last_slash = strpos($model, '/') !== FALSE) {
            $subdir = substr($model, 0, $last_slash + 1);
            $model = substr($model, $last_slash + 1);
        }

        if (!$alias) $alias = strtolower($model);

        if (in_array(strtolower($subdir.$model), $this->_loaded_models)) {
            //already loaded : create a copy of the instance
            log_message('debug', "create copy of library(already loaded)");
            $clone = array_search(strtolower($subdir.$model), $this->_loaded_libs);
            if (isset($this->instance->$alias)) {
                //alias-token : error
                log_message('debug', "Error: Alias already token!");
                return;
            }
            $this->instance->$alias = clone $this->instance->$clone;
            return;
        }

        if (isset($this->instance->$alias)) {
            //alias-token : error
            log_message('debug', "Error: Alias already token!");
            return;
        }

        foreach ($this->_model_paths as $path) {
            //for linux and mac
            if (file_exists($end_path = $path.$subdir.strtolower(ucfirst($model)).'.php')) {
                log_message('debug', "found(ucfirst version)");

                $this->_model($model, $subdir, $end_path, $config, $alias);
                return;
            }

            if (file_exists($end_path = $path.$subdir.strtolower($model).'.php')) {
                log_message('debug', "found(lower version)");

                $this->_model($model, $subdir, $end_path, $config, $alias);
                return;
            }

            //check if classname is path
            if (empty($subdir)) {
                //for linux and mac
                if (file_exists($end_path = $path.$model.'/'.strtolower(ucfirst($model)).'.php')) {
                    log_message('debug', "found(ucfirst version) in subfolder like clasname");

                    $this->_model($model, $class, $end_path, $config, $alias);
                    return;
                }

                if (file_exists($end_path = $path.$model.'/'.strtolower($model).'.php')) {
                    log_message('debug', "found(lower version) in subfolder like clasname");

                    $this->_model($model, $class, $end_path, $config, $alias);
                    return;
                }
            }
        }

        //no file found
        echo "Model \"$model\" cought not be found!";
    }

    /**
     * Loads a Helper-File
     *
     * @param string File-Name(Path) no 'helpers/' and '_helper.php'
     * @return void
     **/
    public function helper($helper) {
        if (is_array($helper)) {
            foreach ($helper as $help) {
                $this->helper($help);
            }
            return;
        }

        if (in_array($helper, $this->_loaded_helpers)) {
            log_message('debug', "Helper already loaded!");
            return;
        }

        //logic to load extended helpers

        foreach ($this->_helper_paths as $path) {
            if (file_exists($end_path = $path.$helper.'_helper.php')) {
                include_once $end_path;
                return;
            }
        }

        //no file found
        echo "Helper \"$helper\" cought not be found!";
    }

    /**
     * Loads a Config-File
     *
     * @param string Name of the file( and group)
     * @param boolean use groups
     * @param boolean Return the array OR save in config-array
     * @return void/array
     **/
    public function config($name, $use_groups = false, $return = false) {
        if ($return) {
            foreach ($this->_config_paths as $path) {
                if (file_exists($path.$name.'.ini')) {
                    $config = parse_ini_file(APPPATH.'config/'.$name.'.ini', $use_groups);
                    //var_dump($ini);
                    return $config;
                }
            }
            return NULL;
        } else {
            $this->instance->config->read_config($name, $use_groups);
        }
    }

    /**
     * Loads a Complete-Language
     *
     * @param string Name of the language(folder)
     * @return void
     **/
    public function language($name) {
        foreach ($this->_language_paths as $path) {
            if (file_exists($ini = $path.$name.'/lang.ini')) {
                $ini = parse_ini_file($ini, true);
                if (isset($ini['Files'])) {
                    $files = $ini['Files'];
                    $type = isset($ini['Language']['type']) ? $ini['Language']['type'] : 'array';

                    if ($type == 'array') $this->_lang_array($files, $path.$name.'/');
                    elseif ($type == 'gettext') $this->_lang_gettext($files, $path.$name.'/');
                }
            }
        }
    }

    //-----------------------------------------------------------------------------------

    private function _library($class, $subdir, $full_path, $config, $alias) {
        require_once $full_path;
        if (class_exists($endclass = $class) || class_exists($endclass = 'SF_'.$class)) {
            $this->_loaded_libs[$alias] = strtolower($subdir.$class);
            
            //load config if not parsed
            if (is_null($config)) $config = $this->config($class, true, true);

            //instanciate
            $this->instance->$alias = new $endclass($config);
            return;
        }
        //class does not exist
        echo "Library \"$class\" cought not be found!";
    }

    private function _model($model, $subdir, $full_path, $config, $alias) {
        require_once $full_path;
        if (class_exists($endclass = $model.'_Model')) {
            $this->_loaded_model[$alias] = strtolower($subdir.$model);
            
            //load config if not parsed
            if (is_null($config)) $config = $this->config($model, true, true);

            //instanciate
            $this->instance->$alias = new $endclass($config);
            return;
        }
        //class does not exist
        echo "Model \"$model\" cought not be found!";
    }

    private function _lang_array($files, $path) {
        foreach ($files as $name => $file) {
            if (file_exists($path.$file)) {
                ob_start();
                include $path.$file;
                if (isset($lang)) {
                    if (is_array($lang)) {
                        $this->instance->lang->add_section($name, $lang);
                    } else {
                        $this->instance->lang->add_section($name);
                    }
                    unset($lang);
                } else {
                    $this->instance->lang->add_section($name);
                }
                ob_end_clean();
            }
        }
    }

    /* @TODO add gettext functionality */
    private function _lang_gettext($files, $path) {
        
    }



}

