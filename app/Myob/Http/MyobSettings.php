<?php

namespace App\Myob\Http;

class MyobSettings
{


	public string|null $token_uri;
	public string|null $uri;
	public string|null $key; // Client KEY
	public string|null $client_secret;
	public string|null $grant_type;
	public string|null $code;
	public string|null $redirect_uri;
	public string|null $cftoken;
	public string|null $version;
	public string|null $refresh_token;

	public function __construct()
	{

		$this->token_uri     = env('MYOB_AUTH_URI');
		$this->uri           = env('MYOB_URI');
		$this->key           = env('MYOB_API_KEY');
		$this->client_secret = env('MYOB_CLIENT_SECRET');
		$this->code          = env('MYOB_AUTH_CODE');
		$this->redirect_uri  = env('MYOB_REDIRECT_URI');
		$this->cftoken       = env('MYOB_CFTOKEN');
		$this->version       = env('MYOB_VERSION');
		$this->refresh_token = env('MYOB_REFRESH_TOKEN');
	}

	public function getEndpointURI($end_point, $guid = ''): string
	{
		return trim($this->uri, '/') . '/' . $end_point . '/' . $guid;
	}
	public function postEndpointURI($end_point): string
	{
		return trim($this->uri, '/') . '/' . $end_point;
	}
}
