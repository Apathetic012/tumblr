<?php
session_start();

require '../vendor/autoload.php';
use Tumblr\Main as Tumblr;

if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: ./clean.php');
    exit;
}

$access_token = $_SESSION['access_token'];

$config = array(
        'consumerKey'       => '',
        'consumerSecretKey' => '',
        'baseUrl'           => 'http://api.tumblr.com/v2/blog/myblog.tumblr.com'
);

$api = new Tumblr($config, $access_token['oauth_token'], $access_token['oauth_token_secret']);

// Retrieve blog info
$api->get('/info', array(
	'base-hostname' => 'myblog.tumblr.com',
	'api_key' => $config['consumerKey']
));

// Retrieve blog image
$api->get('/avatar', array(
	'base-hostname' => 'myblog.tumblr.com',
	'size' => 40
));

// Post a text article
$api->post('/post', array(
	'type' => 'text',
	'tags' => 'tags,separated,by,comma',
	'title' => 'Hello World',
	'body' => 'This is my first post!'
));

// Post an image
$api->post('/post', array(
	'type' => 'photo',
	'caption' => 'look who this is!',
	'data' => array(
		file_get_contents('image.jpg')
	)
));

# http://www.tumblr.com/api_docs#posting