<?php

namespace SocialSignIn\ExampleCrmIntegration\Authentication;

use Slim\Http\Request;
use Slim\Http\Response;

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

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $query = $request->getQueryParams();

        if (!isset($query['sig'])
            || !is_string($query['sig'])
            || !isset($query['expires'])
            || !is_string($query['expires'])
            || !ctype_digit($query['expires'])
        ) {
            return $response->withJson(['status' => 'error', 'error' => 'missing or invalid sig or expires params'], 400);
        }

        $signature = $query['sig'];
        unset($query['sig']);

        $expected = hash_hmac('sha256', http_build_query($query), $this->sharedSecret);

        if ($expected != $signature) {
            error_log("Signature mismatch: $expected vs $signature");
            return $response->withJson(['status' => 'error', 'error' => 'Signature mismatch'], 403);
        }

        $now = time();
        if ($now > $query['expires']) {
            error_log("Request has expired. Clock skew or attempt at replay attack. $now vs {$query['expires']}");
            return $response->withJson(['status' => 'error', 'error' => 'request expired'], 403);
        }

        return $next($request, $response);
    }
}
