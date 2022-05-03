<?php
/*
 *  Base Controller
 *  Loads models and views
*/

class Controller {

    private $data = [];

    /**
     * Loads a model into the controller
     *
     * @access public
     * @param string $model
     * @return void
     */
    public function model($model) {
        // Require model file
        require_once "../app/models/" . $model . ".php";

        // Instantiate the model
        return new $model();

    }

    /**
     * Loads a view into the controller
     *
     * @access public
     * @param string $model
     * @return void
     */
    public function view($view, $data=[]) {
        // Check for the view file
        if(file_exists("../app/views/" . $view . ".php")) {
            require_once "../app/views/" . $view . ".php";
        } else {
            // View does not exist
            die("View does not exist");
        }
    }

    /*
        Get/Set Data
    */

    /**
     * Sets data to the $this->data variable
     *
     * @param string $key
     * @param string $value
     * @param boolean $clean
     * @return void
     */
    public function setData($key, $value, $clean=false) {
        if($clean) {
            $this->data[$key] = htmlentities($value);
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Set multiple data values from array.
     *
     * @param assoc $arr
     * @return void
     */
    public function setMultiData($arr) {
        foreach ($arr as $key => $value) {
            $this->setData($key, $value);
        }
    }

    /**
     * Gets data from $this->data and echoes or returns it.
     * If data does not exist, an empty string is returned.
     *
     * @param string $key
     * @param boolean $echo
     * @return string
     */
    public function getData($key, $echo=false) {
        $data = $this->data[$key] ?? '';
        if($echo) {
            echo $data;
        } else {
            return $data;
        }
    }

    /**
     * Gets nested data from $this->data from Assoc Arrays or objects.
     * The data is echoed or returned it.
     * If data does not exist, an empty string is returned.
     *
     * @param string $key
     * @param string $value
     * @param boolean $echo
     * @return string
     */
    public function getNestedData($key, $value, $echo=false) {
        $data = '';
        if(isset($this->data[$key])) {
            if(is_object($this->data[$key])) {
                $data = $this->data[$key]->$value ?? '';
            } else {
                $data = $this->data[$key][$value] ?? '';
            }
        }
        if($echo) {
            echo $data;
        } else {
            return $data;
        }
    }

    /**
     * Checks if key is set exists in $this->data.
     * Optional functionality to check assoc array or object 
     * value.
     *
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function checkData($key, $value=null) {
        if($value && isset($this->data[$key])) {
            if(is_object($this->data[$key])) {
                return (isset($this->data[$key]->$value) && !empty($this->data[$key]->$value));
            } else {
                return (isset($this->data[$key][$value]) && !empty($this->data[$key][$value]));
            }
        } else {
            return (isset($this->data[$key]) && !empty($this->data[$key]));
        }
    }
}