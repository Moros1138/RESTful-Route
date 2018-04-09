# RESTful-Route

```php
$r->add_route('GET', 'users', function($args) {
	
	extract($args);
	
	return array(
		'code' => 200,
		'data' => array('message' => 'It worked in GET')
	);
});

// SECOND EXAMPLE
$r->add_route('GET', 'users/{id}', function($args) {
	
	extract($args);
	
	return array(
		'code' => 200,
		'body' => array('message' => "It worked in GET for ID ({$id})")
	);
	
});

// does route comparisons and responses
$r->run();

```
