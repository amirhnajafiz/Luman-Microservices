<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth0\SDK\Exception\InvalidTokenException;

class Auth0Middleware
{
    /**
     * Run the request filter.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws InvalidTokenException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();
        if(!$token) {
            return response()->json('No token provided', 401);
        }

        $this->validateToken($token);

        return $next($request);
    }

    public function validateToken($token)
    {
        try {
            $jwksUri = env('AUTH0_DOMAIN') . '.well-known/jwks.json';
            $jwksFetcher = new JWKFetcher(null, [ 'base_uri' => $jwksUri ]);
            $signatureVerifier = new AsymmetricVerifier($jwksFetcher);
            $tokenVerifier = new TokenVerifier(env('AUTH0_DOMAIN'), env('AUTH0_AUD'), $signatureVerifier);

            $decoded = $tokenVerifier->verify($token);
        }
        catch(InvalidTokenException $e) {
            throw $e;
        }
    }
}
