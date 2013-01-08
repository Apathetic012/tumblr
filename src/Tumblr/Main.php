<?php
namespace Tumblr;

use Eher\OAuth\Consumer;
use Eher\OAuth\HmacSha1;
use Eher\OAuth\Request;
use Eher\OAuth\Util;

class Main {

	private $requestTokenUrl = 'http://www.tumblr.com/oauth/request_token';
	private $AuthorizeUrl = 'http://www.tumblr.com/oauth/authorize';
	private $accessTokenUrl = 'http://www.tumblr.com/oauth/access_token';
	private $config;
	private $data;
	private $token;
	private $consumer;
	private $oAuthToken;
	private $signatureMethod;

	public function __construct($config, $oAuthToken = null, $oAuthSecret = null)
	{
		if (
			! isset($config['consumerKey']) ||
			! isset($config['consumerSecretKey']) ||
			! isset($config['baseUrl'])
		) {
			$keys = array_keys($config);
			$missing = array_diff(array('consumerKey','consumerSecretKey','baseUrl'), array_keys($config));
			throw new TumblrException('Missing configuration: ' . implode(',', $missing));
		}

		$this->config = $config;
		$this->consumer = new Consumer($config['consumerKey'], $config['consumerSecretKey']);
		$this->signatureMethod = new HmacSha1();

		if ( ! is_null($oAuthToken) and ! is_null($oAuthSecret)) {
			$this->token = new Consumer($oAuthToken, $oAuthSecret);
		} else {
			$this->token = null;
		}
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getAccessToken($oauthVerifier)
	{
		$params = array('oauth_verifier' => $oauthVerifier);
		$token = $this->makeRequest($this->accessTokenUrl, 'POST', $params);

		return $token;
	}

	public function getRequestToken($callback = false)
	{
		$params = array();

		if ( $callback) {
			$params['oauth_callback'] = $callback;
		}

		$token = $this->makeRequest($this->requestTokenUrl, 'GET', $params);
		$this->token = new Consumer($token['oauth_token'], $token['oauth_token_secret']);

		return $token;
	}

	public function getAuthorizeUrl($oAuthToken)
	{
		return $this->AuthorizeUrl . '?oauth_token=' . $oAuthToken;
	}

	protected function makeRequest($url, $method, $params)
	{
		$request = Request::from_consumer_and_token(
			$this->consumer,
			$this->token,
			$method,
			$url,
			$params
		);

		$request->sign_request($this->signatureMethod, $this->consumer, $this->token);

		switch($method) {
			case 'POST':
				$url = $request->get_normalized_http_url();
				$this->data = $request->to_postdata();
				break;
			default:
				$url = $request->to_url();
				break;

		}

		$response = $this->curl($url , $method);

		return Util::parse_parameters($response);
	}

	protected function curl($url, $method)
	{
		$ch = curl_init();
		switch ($method) {
			case 'GET':
				curl_setopt_array($ch, array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true
				));
				break;
			case 'POST':
				curl_setopt_array($ch, array(
					CURLOPT_URL => $url,
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $this->data,
					CURLOPT_RETURNTRANSFER => true
				));
		}

		$response = curl_exec($ch);

		return $response;
	}

	protected function callApi($url, $method, $params)
	{
		$response = call_user_func_array(
			array($this, 'makeRequest'),
			array(
				$this->config['baseUrl'].$url,
				$method,
				$params
			)
		);

		return json_decode(array_keys($response)[0]);
	}

	public function __call($method, $params)
	{
		$parameters = array();

		if (isset($params[1])) {
			$parameters = array_merge($parameters, $params[1]);
		}

		return call_user_func_array(
			array($this, 'callApi'),
			array(
				$params[0],
				strtoupper($method),
				$parameters
		));
	}
}

class TumblrException extends \Exception {}