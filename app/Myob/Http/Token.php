<?php

namespace App\Myob\Http;

/**
 * Represents a Bearer token issued by an OAuth2 token endpoint.
 */
class Token
{

	/**
	 * Token type, usually `Bearer`.
	 */
	public ?string $type = null;

	public ?int $expiresIn = null;

	public ?int $expiresOn = null;

	public ?int $notBefore = null;

	/**
	 * Resource URI for which the token was granted.
	 */
	public ?string $resource = null;

	/**
	 * Token value.
	 */
	public ?string $token   = null;
	public ?string $refresh = null;

	/**
	 * Constructs a new Token object from a JSON received from an OAuth2 token endpoint.
	 *
	 * @param string $json
	 *
	 * @return Token
	 */
	public static function createFromJson(string $json): Token
	{
		try {
			$tokenArray = \GuzzleHttp\json_decode($json, true);
		} catch (\InvalidArgumentException $e) {
			return new Token();
		}

		$token            = new Token();
		$token->type      = $tokenArray['token_type'] ?? null;
		$token->expiresIn = isset($tokenArray['expires_in']) ? (int) $tokenArray['expires_in'] : null;
		$token->expiresOn = isset($tokenArray['expires_on']) ? (int) $tokenArray['expires_on'] : null;
		$token->notBefore = isset($tokenArray['not_before']) ? (int) $tokenArray['not_before'] : null;
		$token->resource  = $tokenArray['resource'] ?? null;
		$token->token     = $tokenArray['access_token'] ?? null;

		return $token;
	}

	public static function fetchToken()
	{
		$myobSettingController = "App\Http\Controllers\MyobSettingController";
		$myobToken = app($myobSettingController)->index();
		$token            = new Token();
		if ($myobToken) {
			$expiresIn = $myobToken[0]->expire_in;
			$updatedAt = $myobToken[0]->updated_at;
			$updatedAtDateTime = new DateTime($updatedAt);
			$expiresIn = $expiresIn - 120;
			// Create a DateInterval object representing the duration of expiresIn
			//$expiresInterval = DateInterval::createFromDateString($expiresIn . ' seconds');

			// Add the expiresIn duration to the update_at timestamp
			//$expirationDateTime = $updatedAtDateTime->add($expiresInterval);

			$expirationTimestamp = $updatedAtDateTime->getTimestamp() + $expiresIn;
			$currentTimestamp = time();
			if ($currentTimestamp < $expirationTimestamp) {
				$token->type      = $myobToken[0]->token_type;
				$token->expiresIn = $expiresIn;
				$token->expiresOn = $expirationTimestamp;
				$token->notBefore = $updatedAtDateTime->getTimestamp();
				$token->token     = $myobToken[0]->access_token;
				$token->refresh   = $myobToken[0]->refresh_token;
				return $token;
			} else {

				//refresh token
				//update database token
			}
		} else {

			//refresh token
			//store token in database
		}
	}
	/**
	 * Tells whether the token is not expired.
	 *
	 * @param int|null $time Specify time to check the token against. Default is current time.
	 *
	 * @return bool
	 */
	public function isValid(int $time = null): bool
	{
		if ($time === null) {
			$time = time();
		}

		return ($time >= $this->notBefore && $time < $this->expiresOn);
	}
}
