<?php


namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalApiAuth
{

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        // Decode the JWT token
        $decodedToken = $this->decodeJwtToken($token);

        if (!$decodedToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Verify the token's claims (e.g., expiration, issuer, etc.)
        if (!$this->verifyTokenClaims($decodedToken)) {
            return response()->json(['error' => 'Invalid token claims'], 401);
        }

        // If everything checks out, continue with the request
        return $next($request);
    }

    private function decodeJwtToken($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null; // Invalid token format
        }

        $payload = $parts[1];

        try {
            $decoded = base64_decode(strtr($payload, '-_', '+/'), true);

            if ($decoded === false) {
                throw new Exception('Invalid base64 encoding');
            }

            return json_decode($decoded, true);
        } catch (Exception $e) {
            return null;
        }
    }

    private function decodeJwtTokenOld($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null; // Invalid token format
        }

        $payload = $parts[1];

        // Add padding for base64 if needed
        $payload = str_pad($payload, strlen($payload) + (4 - strlen($payload) % 4) % 4, '=', STR_PAD_RIGHT);

        $decoded = base64_decode(strtr($payload, '-_', '+/'));

        if ($decoded === false) {
            return null;
        }

        return json_decode($decoded, true);
    }

    private function verifyTokenClaims($decodedToken)
    {
        // Check token expiration
        if (isset($decodedToken['exp']) && $decodedToken['exp'] < time()) {
            return false;
        }

        // Check token issuer (if applicable)
        // if (isset($decodedToken['iss']) && $decodedToken['iss'] !== 'your-issuer') {
        //     return false;
        // }

        // Check JTI (if applicable)
        if (isset($decodedToken['jti'])) {
            $tokenRecord = DB::table('oauth_access_tokens')->where('id', $decodedToken['jti'])->first();

            if (!$tokenRecord) {
                return false;
            }
        }

        return true;
    }

    public function handleOld($request, Closure $next)
    {
        $a = 0; // Replace with your actual logic to determine the value of $a

        if ($a == 0) {
            return response()->json(['error' => 'Unauthorized or invalid condition'], 401);
        }

        // If $a is 1, continue with the request
        return $next($request);

        /*$user = Auth::guard('external_api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return $next($request);
        */

        // Get the Bearer token (without the "Bearer " prefix)
        $token = $request->bearerToken();

        // Function to decode JWT payload
        $decodeJwtPayload = function ($jwt) {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                return null; // Invalid token format
            }

            $payload = $parts[1];

            // Add padding for base64 if needed
            $payload = str_pad($payload, strlen($payload) + (4 - strlen($payload) % 4) % 4, '=', STR_PAD_RIGHT);

            $decoded = base64_decode(strtr($payload, '-_', '+/'));
            if ($decoded === false) {
                return null;
            }

            return json_decode($decoded, true);
        };

        $tokenRecord = null;
        $jti = null;

        if ($token) {
            $payload = $decodeJwtPayload($token);
            if ($payload && isset($payload['jti'])) {
                $jti = $payload['jti'];
                $tokenRecord = DB::table('oauth_access_tokens')->where('id', $jti)->first();
            }
        }

        Log::debug('DEBUG ROUTE HIT');
        Log::debug('Bearer Token: ' . $token);
        Log::debug('Token JTI: ' . ($jti ?? 'N/A'));
        Log::debug('Token DB Record: ' . json_encode($tokenRecord));

        // Continue with the request
        return $next($request);

    }

}
