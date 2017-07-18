<?php

namespace SocialSignIn\ExampleCrmIntegration\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SignatureAuthentication
{

    /**
     * @var string
     */
    private $sharedSecret;

    /**
     * @param string $sharedSecret
     *
     * @throws \Exception
     */
    public function __construct($sharedSecret)
    {
        if (!is_string($sharedSecret) || empty($sharedSecret)) {
            throw new \Exception('Expected $sharedSecret to be non-empty string.');
        }

        $this->sharedSecret = $sharedSecret;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $query = $request->getQueryParams();

        if (!isset($query['sig'])
            || !is_string($query['sig'])
            || !isset($query['expires'])
            || !is_string($query['expires'])
            || !ctype_digit($query['expires'])
        ) {
            return $response->withStatus(400);
        }

        ksort($query);

        $signature = $query['sig'];
        unset($query['sig']);

        if (!hash_equals(hash_hmac('sha256', join(':', $query), $this->sharedSecret), $signature)) {
            return $response->withStatus(403);
        }

        if (time() > $query['expires']) {
            return $response->withStatus(403);
        }

        try {
            return $next($request, $response);
        } catch (\Exception $e) {
            return $response->withStatus(500);
        }
    }
}
