# apathetic/tumblr

API wrapper for the [Tumblr v2 API](www.tumblr.com/api_docs)

Todo
-----
 - <del>docblock</del>
 - tests
 - <del>examples</del>

Install
-----
Via [Composer](http://getcomposer.org):

```json
{
    "require": {
        "apathetic/tumblr": ">=1.0.0"
    }
}
```

Usage
----

```php
$config = array(
    'consumerKey' => '',
    'consumerSecretKey' => '',
    'baseUrl' => ''
);

// handle oauth

// use the oauth tokens
$api = new Tumblr($config, $access_token['oauth_token'], $access_token['oauth_token_secret']);

// post an image
$params = array(
	'type' => 'photo',
	'caption' => 'I saw Justin Bieber!!!!',
	'tags' => 'omg, omgomg, omgomgomg'
	'data' => array(
		file_get_contents('stolenphoto.jpg')
	)
);
$request = $api->post('/post', $params);
/* To be continued */
```

Credits
---

Based from [twitter-oauth](https://github.com/ruudk/twitteroauth/).