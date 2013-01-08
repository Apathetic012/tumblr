<?php
session_start();

require '../vendor/autoload.php';
use Tumblr\Main as Tumblr;

$access_token = $_SESSION['access_token'];

$config = array(
        'consumerKey'       => '',
        'consumerSecretKey' => '',
        'baseUrl'           => 'http://api.tumblr.com/v2/blog/myblog.tumblr.com'
);

$api = new Tumblr($config);
$token = $api->getRequestToken('http://play.dev/callback.php');

$_SESSION['oauth_token'] = $oAuthUrl = $token['oauth_token'];
$_SESSION['oauth_token_secret'] = $token['oauth_token_secret'];

header('Location: ' . $api->getAuthorizeUrl($oAuthUrl));