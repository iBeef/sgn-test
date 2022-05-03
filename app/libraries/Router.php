<?php

// /*
//     Router class
//     Handles all routes passed in from the url.
// */

class Router {

    private $url;
    private $requestMethod;
    private $controller;
    private $getRoutes = [];
    private $postRoutes = [];
    private $putRoutes = [];
    private $deleteRoutes = [];
    private $wildCards = array(
        'int' => '/^[0-9]+$/',
        'any' => '/^[0-9A-Za-z]+$/'
    );

    /**
     * Construct function - Fetches url and request method then the class is instantiated.
     * 
     * @access public
     * @return void
     */
    public function __construct() {
        $this->url = '/' . $this->getUrl();
        $this->requestMethod = $this->getRequestMethod();
    }

    /*
        Callable functions.
    */

    /**
     * Stores the GET route and user defined function to the $getRoutes assoc array.
     *
     * @access public
     * @param string $route
     * @param  function $fn
     * @return void
     */
    public function get($route, $fn) {
        $this->getRoutes[$route] = $fn; 
    }
    
    /**
     * Stores the POST route and user defined function to the $postRoutes assoc array.
     *
     * @access public
     * @param string $route
     * @param  function $fn
     * @return void
     */
    public function post($route, $fn) {
        $this->postRoutes[$route] = $fn; 
    }

    /**
     * Stores the PUT route and user defined function to the $putRoutes assoc array.
     *
     * @access public
     * @param string $route
     * @param  function $fn
     * @return void
     */
    public function put($route, $fn) {
        $this->putRoutes[$route] = $fn; 
    }

    /**
     * Stores the DELETE route and user defined function to the $deleteRoutes assoc array.
     *
     * @access public
     * @param string $route
     * @param  function $fn
     * @return void
     */
    public function delete($route, $fn) {
        $this->deleteRoutes[$route] = $fn; 
    }

    /**
     * Called at the bottom of index.php to run the class and route the url request.
     *
     * @access public
     * @return void
     */
    public function run() {
        $matchingRoute = FALSE;
        $routeType = strtolower($this->requestMethod) . "Routes";
        foreach(array_keys($this->$routeType) as $route) {
            $match = $this->matchRoutes($route);
            if(is_array($match)) {
                $matchingRoute = True;
                if(count($match) > 0) {
                    call_user_func_array($this->$routeType[$route], [$this, $match]);
                    die();
                } else {
                    call_user_func_array($this->$routeType[$route], [$this]);
                    die();
                }
                break;
            }
        }
        call_user_func_array($this->getRoutes['/404'], [$this]);
        die();
    }

    /*
        Internal "Helper" functions
    */

    /**
     * Fetches the url from index.php
     *
     * @access private
     * @return void
     */
    private function getUrl() {
        if(isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return $url;
        }
    }

    /**
     * Fetches the request method for the current request.
     *
     * @access private
     * @return void
     */
    private function getRequestMethod() {
        $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        if($requestMethod == 'post' && isset($_POST['_method'])) {
            $postMethod = strtolower($_POST['_method']);
            return ($postMethod == 'put' || $postMethod == 'delete') ? $_POST['_method'] : 'GET';
        } elseif($requestMethod == 'post') {
            return 'POST';
        } else {
            return 'GET';
        }
    }

    /**
     * Loads the controller and method specified by the user and passes in any supplied params.
     *
     * @param string $controller
     * @param string $method
     * @param array $params
     * @return void
     */
    public function loadController($controller, $method, $params=[]) {
        $controller = ucwords($controller) . "Controller";
        // Require the controller
        require_once "../app/controllers/" . $controller . ".php";
        // Instantiate the controller
        $this->controller = new $controller();
        // Call the specified method and pass in the params
        call_user_func_array([$this->controller, $method], $params);
    }

    /**
     * Check a stored route against the current url for a match
     *
     * @access public
     * @param string $route
     * @return array, boolean
     */
    private function matchRoutes($route) {
        $routeVars = array();
        $request = explode('/', $this->url);
        $route = explode('/', $route);
     
        if (count($request) == count($route)) {
            foreach ($route as $index => $routeSegment) {
                if ($routeSegment == $request[$index]) {
                    continue;
                }
                elseif ($routeSegment != '' && $routeSegment[0] == '(' && substr($routeSegment, -1) == ')') {
                    $strip = str_replace(array('(', ')'), '', $routeSegment);
                    $dynamicVar = explode(':', $strip);
                    if (array_key_exists($dynamicVar[0], $this->wildCards)) {
                        $pattern = $this->wildCards[$dynamicVar[0]];
                        if (preg_match($pattern, $request[$index])) {
                            if (isset($dynamicVar[1])) {
                            $routeVars[$dynamicVar[1]] = $request[$index];
                            }
                            continue;
                        }
                    }
                }
                return false;
            }
            return $routeVars;
        }
        return false;
    }
}