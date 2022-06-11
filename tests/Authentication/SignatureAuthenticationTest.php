<?php

namespace SocialSignIn\Test\ExampleCrmIntegration\Authentication;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use SocialSignIn\ExampleCrmIntegration\Authentication\SignatureAuthentication;

/**
 * @covers \SocialSignIn\ExampleCrmIntegration\Authentication\SignatureAuthentication
 */
class SignatureAuthenticationTest extends \PHPUnit\Framework\TestCase
{

    private $sharedSecret;
    private $middleware;

    public function setUp(): void
    {
        $this->sharedSecret = md5(random_bytes(32));
        $this->middleware = new SignatureAuthentication($this->sharedSecret);
    }

    /**
     * @expectedException \TypeError
     */
    public function testInvalidSharedSecret()
    {
        new SignatureAuthentication(null);
    }

    public function testMissingQueryParameters()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => ''
            ])
        );

        /** @var Response $response */
        $response = call_user_func($this->middleware, $request, new Response(), function () {
        });

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testInvalidSignature()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => $this->sign(['id' => '1234'], 'incorrect-secret')
            ])
        );

        /** @var Response $response */
        $response = call_user_func($this->middleware, $request, new Response(), function () {
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testExpired()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => $this->sign(['id' => '1234'], $this->sharedSecret, -3600)
            ])
        );

        /** @var Response $response */
        $response = call_user_func($this->middleware, $request, new Response(), function () {
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testSuccess()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => $this->sign(['id' => '1234'], $this->sharedSecret)
            ])
        );

        $cb = function ($request, Response $response) {
            return $response->withStatus(200);
        };

        /** @var Response $response */
        $response = call_user_func($this->middleware, $request, new Response(), $cb);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCallbackThrows()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => $this->sign(['id' => '1234'], $this->sharedSecret)
            ])
        );

        $cb = function ($request, Response $response) {
            throw new \Exception('asdf');
        };


        $this->expectException(\Exception::class);
        $response = call_user_func($this->middleware, $request, new Response(), $cb);
    }

    private function sign(array $params, $secret, $ttl = 3600): string
    {
        $params['expires'] = time() + $ttl;

        $params['sig'] = hash_hmac('sha256', http_build_query($params), $secret);
        return http_build_query($params);
    }
}
