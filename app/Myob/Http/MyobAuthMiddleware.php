<?php

namespace App\Myob\Http;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Exception\RequestException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Log;

class MyobAuthMiddleware
{

	/**
	 * OData service settings.
	 */
	protected MyobSettings $settings;

	protected Token $token;

	protected ?HttpClient $httpClient = null;

	/**
	 * OnlineAuthMiddleware constructor.
	 *
	 * @param MyobSettings $settings
	 */
	public function __construct(MyobSettings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Constructs an HTTP client for the middleware.
	 */
	public function getHttpClient(): HttpClient
	{
		if ($this->httpClient instanceof HttpClient) {
			return $this->httpClient;
		}

		// $verify = $this->settings->caBundle;
		// if ( $verify === null ) {
		// $verify = $this->settings->tlsVerifyPeers;
		// if ( $verify && $this->settings->caBundlePath !== null ) {
		// $verify = $this->settings->caBundlePath;
		// }
		// }

		// $this->httpClient = new HttpClient(
		// array(
		// 'verify' => $verify,
		// )
		// );
		$headers = [
			'x-myobapi-key'		 =>  $this->settings->key,
			'x-myobapi-version'  =>  $this->settings->version,
			'Accept-Encoding'    => 'gzip,deflate',
			'x-myobapi-cftoken'  =>  $this->settings->cftoken,
			'Authorization'		 => "Bearer " . $this->settings->code
		];
		$this->httpClient = new HttpClient([
			'base_uri' => $this->settings->uri,
			'headers' => $headers
		]);

		return $this->httpClient;
	}
	public function postHttpClient($body): HttpClient
	{
		if ($this->httpClient instanceof HttpClient) {
			return $this->httpClient;
		}

		// $verify = $this->settings->caBundle;
		// if ( $verify === null ) {
		// $verify = $this->settings->tlsVerifyPeers;
		// if ( $verify && $this->settings->caBundlePath !== null ) {
		// $verify = $this->settings->caBundlePath;
		// }
		// }

		// $this->httpClient = new HttpClient(
		// array(
		// 'verify' => $verify,
		// )
		// );
		$headers = [
			'x-myobapi-key'		 =>  $this->settings->key,
			'x-myobapi-version'  =>  $this->settings->version,
			'Accept-Encoding'    => 'gzip,deflate',
			'x-myobapi-cftoken'  =>  $this->settings->cftoken,
			'Authorization'		 => "Bearer " . $this->settings->code
		];
		$this->httpClient = new HttpClient([
			'base_uri' => $this->settings->uri,
			'headers' => $headers,
			'body' => $body
		]);

		return $this->httpClient;
	}

	public function refreshToken()
	{
		$httpClient = new HttpClient();
		$response = $httpClient->post($this->settings->token_uri, [
			'form_params' => [
				'grant_type'    => 'refresh_token',
				'client_id'     => $this->settings->key,
				'client_secret' => $this->settings->client_secret,
				'refresh_token' => $this->settings->refresh_token,
				'scope'         => 'CompanyFile'
			],
		]);

		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
			$this->settings->code = $data['access_token'];
			$this->settings->refresh_token = $data['refresh_token'];
			$this->httpClient = new HttpClient([
				'base_uri' => $this->settings->uri,
				'headers'  => [
					'x-myobapi-key'     => $this->settings->key,
					'x-myobapi-version' => $this->settings->version,
					'Accept-Encoding'   => 'gzip,deflate',
					'x-myobapi-cftoken' => $this->settings->cftoken,
					'Authorization'     => "Bearer " . $this->settings->code,
				],
			]);
		} else {
			throw new Exception('Failed to refresh token');
		}
	}

	// /**
	// * Provides access to the cache pool to store transient data, e.g. access token, tenant id.
	// */
	// protected function getPool(): CacheItemPoolInterface {
	// return $this->settings->cachePool;
	// }

	/**
	 * Acquires the Bearer token via client credentials OAuth2 flow.
	 *
	 * @throws AuthenticationException
	 */
	protected function acquireToken(): Token
	{

		if ($this->token instanceof Token && $this->token->isValid()) {
			return $this->token; // Token already acquired and is not expired.
		}

		$myobSettingController = "App\Http\Controllers\MyobSettingController";
		$myobToken = app($myobSettingController)->index();

		$settings             = $this->settings;
		$this->token          = new Token();
		if ($myobToken) {
			$expiresIn = $myobToken[0]->expire_in;
			$updatedAt = $myobToken[0]->updated_at;
			$updatedAtDateTime = new DateTime($updatedAt);
			$expiresIn = $expiresIn - 120;

			$expirationTimestamp = $updatedAtDateTime->getTimestamp() + $expiresIn;
			$currentTimestamp = time();
			if ($currentTimestamp < $expirationTimestamp) {
				$this->token->type      = $myobToken[0]->token_type;
				$this->token->expiresIn = $expiresIn;
				$this->token->expiresOn = $expirationTimestamp;
				$this->token->notBefore = $updatedAtDateTime->getTimestamp();
				$this->token->token     = $myobToken[0]->access_token;
				$this->token->refresh   = $myobToken[0]->refresh_token;
				return $this->token;
			}
			$settings->refresh_token = $myobToken[0]->refresh_token;
		}


		// $pool = $this->getPool();

		// if ( $cache->isHit() ) {
		// $token = $cache->get();
		// if ( $token instanceof Token && $token->isValid() ) {
		// $this->token = $token;
		// $settings->logger->debug( 'Loaded a non-expired access token from cache' );

		// return $token;
		// }

		// $pool->deleteItem( $cacheKey );
		// }

		$httpClient = $this->getHttpClient();

		try {

			$requestPayload = array(
				'form_params' => array(
					'grant_type'    => 'refresh_token',
					'client_id'     => $settings->key,
					'client_secret' => $settings->client_secret,
					'refresh_token' => $settings->refresh_token,
				),
			);

			$tokenResponse = $httpClient->post($settings->token_uri, $requestPayload);

			if ($tokenResponse) {
				//update token in database
				if ($myobToken) {
					//request()
					//'access_token', 'refresh_token', 'expires_in', 'token_type'
					app($myobSettingController)->update($tokenResponse, $myobToken->id);
				} else {
					app($myobSettingController)->save($tokenResponse);
				}
			} else {
				return "something went wrong";
			}
			// $settings->logger->debug( 'Retrieved a new access token via ' . $tokenEndpoint );

			// // Check if the request was successful
			// if ( $tokenResponse->successful() ) {
			// Process the response data
			// $data = $response->json();
			// Do something with $data
			// } else {
			// Handle the error response
			// $statusCode = $response->status();
			// Handle error based on status code
			// }
		} catch (\Illuminate\Http\Client\RequestException $e) {
			// Handle request exception
			$statusCode = $e->response->status();
			// Log the exception
			Log::error("HTTP request to https://api.example.com/data failed with status code: $statusCode");
		} catch (\Exception $e) {
			// Handle other exceptions
			// This could be network errors or other unexpected issues
			// Log the exception, or perform appropriate action
			// For example:
			Log::error('An unexpected error occurred: ' . $e->getMessage());
		}

		// $this->token    = Token::createFromJson( $tokenResponse->getBody()->getContents() );
		// $expirationDate = new \DateTime();
		// $expirationDate->setTimestamp( $this->token->expiresOn );
		// $pool->save( $cache->set( $this->token )->expiresAt( $expirationDate ) );

		return $this->token;
	}

	/**
	 * @param $text
	 * Returns base64 url-encode string
	 * @return string
	 */
	function base64UrlEncode($text)
	{
		return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
	}

	// /**
	// * Discards the access token from memory and cache.
	// */
	// public function discardToken(): void {
	// $this->token = null;

	// $settings = $this->settings;

	// $cacheKey = 'msdynwebapi.token.' . sha1( $settings->instanceURI . $settings->applicationID . $settings->applicationSecret ?? $settings->certificatePath );
	// $this->getPool()->deleteItem( $cacheKey );
	// }

	/**
	 * Returns a Guzzle-compliant middleware.
	 *
	 * @return callable
	 *
	 * @see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html
	 */
	public function getMiddleware(): callable
	{
		$self = $this;

		return static function (callable $handler) use ($self) {
			return static function (RequestInterface $request, array $options) use ($self, $handler) {
				$token       = $self->acquireToken();
				$headerValue = $token->type . ' ' . $token->token;
				$newReq      = $request->withHeader('Authorization', $headerValue);

				return $handler($newReq, $options);
			};
		};
	}
}
