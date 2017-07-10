# RESTful-Route

```php
$r->add_route('users', function($args) {
	
	extract($args);
	
	// only on GET
	if(METHOD == 'GET') {
		
		return array(
			'code' => 200,
			'body' => array('message' => 'It worked in GET')
		);
		
	}
	
	// only on POST
	if(METHOD == 'POST') {
		return array(
			'code' => 200,
			'body' => array('message' => 'It worked in POST')
		);
	}
	
	// return method not allowed
	return array( 'code' => 405	);
	
});

// SECOND EXAMPLE
$r->add_route('users/{id}', function($args) {
	
	extract($args);
	
	// only on GET
	if(METHOD == 'GET') {
		
		return array(
			'code' => 200,
			'body' => array('message' => "It worked in GET for ID ({$id})")
		);
		
	}
	
	// only on POST
	if(METHOD == 'POST') {
		return array(
			'code' => 200,
			'body' => array('message' => "It worked in POST for ID ({$id})")
		);
	}
	
	// return method not allowed
	return array( 'code' => 405	);
	
});

// does route comparisons and responses
$r->run();

```
