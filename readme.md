# apathetic/tumblr

API wrapper for the Tumblr v2 API

Todo
-----
 - docblock
 - tests

Install
-----
Via [Composer](http://getcomposer.org):

```json
{
    "require": {
        "apathetic/tumblr": "1.0.0"
    }
}
```

Usage
----

```php
use Tumblr\Main as Tumblr;

$config = array(
    'consumerKey' => '',
    'consumerSecretKey' => '',
    'baseUrl' =>
);

$api = new Tumblr($config);

/* To be continued */
```

Credits
---

Based from [twitter-oauth](https://github.com/ruudk/twitteroauth/).