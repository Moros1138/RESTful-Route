<?php
# @Author: Moros Smith <moros1138@gmail.com>
# @Version: 1.0.0

/*
        Portions of this code was inspired by a demonstration of a simple
        REST API in PHP by Maurits van der Schee, author of PHP-CRUD-API.
        
        The demonstration can be found here:
        https://www.leaseweb.com/labs/2015/10/creating-a-simple-rest-api-in-php/
*/

class RESTful_Route {
	
	function __construct() {
		
		define( 'METHOD', $_SERVER['REQUEST_METHOD']);
		
		// routes array
		$this->routes = array();
		
		// request from browser
		$this->request = (!empty($_SERVER['PATH_INFO'])) ? explode('/', trim($_SERVER['PATH_INFO'], '/')) : array();
		
		// current route (used during comparisons)
		$this->current_route = null;
		
		// body of PUT/POST/PATCH/DELETE/etc
		$this->input = json_decode(file_get_contents('php://input'),true);

		// error codes, used by RESTful_Route->response
		$this->error_codes = array(
			400 => array('short' => 'bad request','long' => 'Bad Request.'),
			401 => array('short' => 'unauthorized','long' => 'You have not been authorized to access this resource. If you think this is in error, check your credentials and try again.'),
			403 => array('short' => 'forbidden','long' => 'You are forbidden from acessing this resource.'),
			404 => array('short' => 'not found','long' => 'The resource you requested does not appear to exist.'),
			405 => array('short' => 'method not allowed','long' => 'The HTTP method you used is not allowed on this resource.')
		);
		
	}
	
	function run() {
		
		// arguments array (for passing to the callback)
		$args = array(
			
			// we want callbacks to have access to the post/patch/delete/put body
			'input' => $this->input
			
		);
		
		// only if we have routes
		if(count($this->routes) > 0) {
			
			// cycle through the routes
			for( $i=0; $i<count($this->routes); $i++ ) {
				
				// explode the current route for easier comparisons
				$this->current_route = explode('/', trim($this->routes[$i]['route'], '/'));
				
				if(count($this->request) == count($this->current_route)) {
					
					// our request and current route have a matching count
					
					// match flag
					$match = true;
					
					// cycle through request and current route to compare
					for($ii=0; $ii < count($this->current_route); $ii++) {
						
						
						if($this->request[$ii] != $this->current_route[$ii]) {
							
							// we don't match BUT, it might be a variable
							
							$arg = null;
							
							// if we have a variable, add it, then continue comapring
							if(preg_match('/{(.*)}/is', $this->current_route[$ii], $arg) == 1) {
								
								// add this variable to the arguments array
								$args[$arg[1]] = $this->request[$ii];
								
								// we have a variable, let's continue comparing
								continue;
								
							}
							
							// we don't have a match
							$match = false;
							
						}
						
					}
					
					// if we have a match, work magic
					if($match) {
						
						// we have a match, let's get a response from the callback
						$response = call_user_func_array($this->routes[$i]['callback'], array('args' => $args));
						
						// Send the response given by the callback.
						$this->response($response);
						
						return;
						
					}
					
				}
				
			}
			
		}

		// if we made it here, 404 not found
		$this->response( array('code' => 404) );
		
	}

	function response($response) {
		
		// if not 200, send header with error status code and short error message
		if($response['code'] != 200) {
			header("HTTP/1.1 {$response['code']} {$this->error_codes[$response['code']]['short']}");
		}

		// JSON content
		header('Content-type: application/json');
		
		// cycle through any custom headers added by the callback.
		if(!empty($reponse['headers'])) {
			foreach($response['headers'] as $header) {
				header($header);
			}
		}
		
		// if not 200, set the response body to the long error message
		if($response['code'] != 200) {
			$response['body'] = array('error' => $this->error_codes[$response['code']]['long']);
		}
		
		// output response body
		echo json_encode($response['body']);
		
		// we're done
		exit;
		
	}

	function add_route($route, $callback) {
		
		// check for duplicate entries and block them from being added
		if(count($this->routes) > 0) {
			
			// cycle through the routes
			for( $i=0; $i<count($this->routes); $i++ ) {
				
				// if the route and callback match for this route, return here
				if(($this->routes[$i]['route'] == $route) && ($this->routes[$i]['callback'] == $callback) ) {
					return false;
				}
				
			}
		
		}
		
		// add the entry to the routes array
		$this->routes[] = array( 'route' => $route, 'callback' => $callback );
		
		return true;
		
	}

	function remove_route($route, $callback) {
		
		// only if we have routes
		if(count($this->routes) > 0) {
			
			// cycle through the routes
			for( $i=0; $i<count($this->routes); $i++ ) {
				
				// if the route and callback match for this route, remove it and return
				if( ($this->routes[$i]['route'] == $route) && ($this->routes[$i]['callback'] == $callback) ) {
					unset($this->routes[$i]);
					return;
				}
				
			}
		
		}
		
		// if we make it here, the provided route/callback combo didn't exist
		
	}
	
}
