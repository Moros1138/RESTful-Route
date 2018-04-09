<?php
# @Author: Moros Smith <moros1138@gmail.com>
# @Version: 1.0.0

/*
        Portions of this code was inspired by a demonstration of a simple
        REST API in PHP by Maurits van der Schee, author of PHP-CRUD-API.
        
        The demonstration can be found here:
        https://www.leaseweb.com/labs/2015/10/creating-a-simple-rest-api-in-php/
*/

class RESTFUL_Route {
    
    function __construct() {
        
        // HTTP Method
        define('METHOD', $_SERVER['REQUEST_METHOD']);
        
        // routes array
        $this->routes = array();
        
        // request from browser
        $this->request = (!empty($_SERVER['PATH_INFO'])) ? explode('/', trim($_SERVER['PATH_INFO'], '/')) : array();
        
        // the current route, used during comparisons
        $this->current_route = null;
        
        // body of PUT/POST/PATCH/DELETE HTTP methods
        $this->input = json_decode(file_get_contents('php://input'), true);
        
    }


    function run() {
        
        $args = array(
            
            // we want callbacks to have access to the PUT/POST/PATCH/DELETE HTTP method body
            'input' => $this->input,
            
        );
        
        // do we have routes?
        if(count($this->routes) > 0) {
            
            // cycle through the routes
            for($i=0; $i<count($this->routes); $i++) {
                
                $this->current_route = explode('/', trim($this->routes[$i]['route'], '/'));
                
                // does the request count match this route's count?
                if(count($this->request) == count($this->current_route)) {
                    
                    // match flag used for logic, default to true
                    $match = true;
                    
                    // cycle through the request and this route to compare
                    for($ii=0; $ii < count($this->current_route); $ii++) {
                        
                        // do we NOT have a match?
                        if($this->request[$ii] != $this->current_route[$ii]) {
                            
                            $arg = null;
                            
                            // do we have an argument?
                            if(preg_match('/{(.*)}/is', $this->current_route[$ii], $arg) == 1) {
                                
                                // add this variable to the arguments array
                                $args[$arg[1]] = $this->request[$ii];
                                
                                // continue comparing this request/route
                                continue;
                                
                            }
                            
                            // we don't have a match
                            $match = false;
                            
                        }
                        
                    }
                    
                    // do we have a match?
                    if($match) {
                        
                        // does the METHOD match this route's method
                        if($this->routes[$i]['method'] == METHOD) {
                            
                            // we have a match, let's get a response from the callback
                            $response = call_user_func_array($this->routes[$i]['callback'], array('args' => $args));
                            
                            
                            $response['type'] = (!empty($response['type'])) ? $response['type'] : 'application/json';
                            
                            // Send the response given by the callback.
                            $this->respond($response['code'], $response['data'], $response['type']);
                            
                        } else {
                            
                            // matched! but method not allowed
                            $this->respond(405, array('error'=>'Method not allowed'));
                            
                        }
                        
                        return;
                        
                    }
                    
                }
                
            }
            
        }

        // if we made it here, 404 not found
        $this->respond(404, array('error'=>'Resource not found'));
        
    }
    
    function respond($code=200, $data='', $type='application/json') {
        
        // set the response code
        http_response_code($code);

        // set the Content-type HTTP header
        header('Content-type: '.$type);
        
        // output response data
        switch($type) {
            
            case 'application/json':
                $data = (!empty($data)) ? $data : array();
                echo json_encode($data);
                break;
                
            case 'text/html':
            case 'text/plain':
            case 'text/css':
            case 'text/javascript':
            default:
                echo $data;
                break;
                
        }
        
        exit;
        
    }

    function add_route($method, $route, $callback) {
        
        // do we have routes?
        if(count($this->routes) > 0) {
            
            // cycle through the routes
            for($i=0; $i<count($this->routes); $i++ ) {
                
                // no duplicates!
                if(($this->routes[$i]['method'] == $method) && ($this->routes[$i]['route'] == $route) && ($this->routes[$i]['callback'] == $callback) ) {
                    return false;
                }
                
            }
        
        }
        
        // add the entry to the routes array
        $this->routes[] = array( 'method' => $method, 'route' => $route, 'callback' => $callback );
        
        return true;
        
    }

    function remove_route($method, $route, $callback) {
        
        // do we have routes?
        if(count($this->routes) > 0) {
            
            // cycle through the routes
            for( $i=0; $i<count($this->routes); $i++ ) {
                
                // does our route/callback match a route/callback within the routes array?
                if(($this->routes[$i]['method'] == $method) && ($this->routes[$i]['route'] == $route) && ($this->routes[$i]['callback'] == $callback) ) {
                    
                    // remove the entry from the routes array
                    unset($this->routes[$i]);
                    
                    return;
                    
                }
                
            }
        
        }
        
    }
    
}
