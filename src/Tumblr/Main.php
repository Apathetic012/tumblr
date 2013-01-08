<?php
namespace Tumblr;

use Eher\OAuth\Consumer;
use Eher\OAuth\HmacSha1;
use Eher\OAuth\Request;
use Eher\OAuth\Util;

class Main {

	/**
	 * Request token Endpoint
	 * @var string
	 */
	private $requestTokenUrl = 'http://www.tumblr.com/oauth/request_token';

	/**
	 * Authorization Endpoint
	 * @var string
	 */
	private $AuthorizeUrl    = 'http://www.tumblr.com/oauth/authorize';

	/**
	 * Access Token Endpoint
	 * @var string
	 */
	private $accessTokenUrl  = 'http://www.tumblr.com/oauth/access_token';

	/**
	 * store the config
	 * @var array
	 */
	private $config = array();

	/**
	 * POST data
	 * @var mixed
	 */
	private $data;

	/**
	 * OAuth token
	 * @var mixed
	 */
	private $token;

	/**
	 * Consumer
	 * @var Eher\OAuth\Consumer
	 */
	private $consumer;

	/**
	 * Signature method
	 * @var Eher\OAuth\HmacSha1
	 */
	private $signatureMethod;

	/**
	 *
	 * @param array  $config
	 * @param string $oAuthToken
	 * @param string $oAuthSecret
	 */
	public function __construct(array $config, $oAuthToken = null, $oAuthSecret = null)
	{
		if (
			! isset($config['consumerKey']) ||
			! isset($config['consumerSecretKey']) ||
			! isset($config['baseUrl'])
		) {
			$keys    = array_keys($config);
			$missing = array_diff(array('consumerKey','consumerSecretKey','baseUrl'), array_keys($config));

			throw new TumblrException('Missing configuration: ' . implode(',', $missing));
		}

		$this->config          = $config;
		$this->consumer        = new Consumer($config['consumerKey'], $config['consumerSecretKey']);
		$this->signatureMethod = new HmacSha1();

		if ( ! is_null($oAuthToken) and ! is_null($oAuthSecret)) {
			$this->token = new Consumer($oAuthToken, $oAuthSecret);
		} else {
			$this->token = null;
		}
	}

	/**
	 * Get OAuth access token
	 * @param  string $oauthVerifier oAuth verifier
	 * @return array
	 */
	public function getAccessToken($oauthVerifier)
	{
		$params = array('oauth_verifier' => $oauthVerifier);
		$token  = $this->makeRequest($this->accessTokenUrl, 'POST', $params);

		return $token;
	}

	/**
	 * Get OAuth request token
	 * @param  mixed $callback Optional callback URL
	 * @return array
	 */
	public function getRequestToken($callback = false)
	{
		$params = array();

		if ($callback) {
			$params['oauth_callback'] = $callback;
		}

		$token       = $this->makeRequest($this->requestTokenUrl, 'GET', $params);
		$this->token = new Consumer($token['oauth_token'], $token['oauth_token_secret']);

		return $token;
	}

	/**
	 * Get Authorize URL
	 * @param  string $oAuthToken
	 * @return string
	 */
	public function getAuthorizeUrl($oAuthToken)
	{
		return $this->AuthorizeUrl . '?oauth_token=' . $oAuthToken;
	}

	/**
	 * Make a request
	 * @param  string $url    API Endpoint URl
	 * @param  string $method HTTP Verb
	 * @param  array  $params
	 * @return array
	 */
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

		switch ($method) {
			case 'GET':
				$url = $request->to_url();
				break;
			case 'POST':
				$url = $request->get_normalized_http_url();
				$this->data = $request->to_postdata();
				break;
		}

		$response = $this->curl($url, $method);

		return Util::parse_parameters($response);
	}

	/**
	 * Do a cURL request
	 * @param  string $url
	 * @param  string $method
	 * @return mixed
	 */
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
				break;
		}

		$response = curl_exec($ch);

		return $response;
	}

	/**
	 * Call an API Method
	 * @param  string $url    URI Segment
	 * @param  string $method HTTP Verb
	 * @param  array  $params
	 * @return obj         	  JSON
	 */
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
		$response = array_keys($response);

		return json_decode($response[0]);
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
			)
		);
	}
}

class TumblrException extends \Exception {}