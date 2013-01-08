<?php
session_start();

require '../vendor/autoload.php';
use Tumblr\Main as Tumblr;

if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  header('Location: ./clean.php');
  exit;
}

$config = array(
        'consumerKey'       => '',
        'consumerSecretKey' => '',
        'baseUrl'           => 'http://api.tumblr.com/v2/blog/myblog.tumblr.com'
);

$api = new Tumblr($config, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
$accessToken = $api->getAccessToken($_REQUEST['oauth_verifier']);

$_SESSION['access_token'] = $accessToken;
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

header('Location: ./index.php');