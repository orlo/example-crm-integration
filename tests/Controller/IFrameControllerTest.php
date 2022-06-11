<?php

namespace SocialSignIn\Test\ExampleCrmIntegration\Controller;

use Mockery as m;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use SocialSignIn\ExampleCrmIntegration\Controller\IFrameController;
use SocialSignIn\ExampleCrmIntegration\Person\Entity;
use SocialSignIn\ExampleCrmIntegration\Person\RepositoryInterface;

/**
 * @covers \SocialSignIn\ExampleCrmIntegration\Controller\IFrameController
 */
class IFrameControllerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var IFrameController
     */
    private $controller;

    /**
     * @var \Twig_Environment|m\Mock
     */
    private $twig;

    /**
     * @var RepositoryInterface|m\Mock
     */
    private $repository;

    public function setUp() : void
    {
        $this->twig = m::mock(\Twig_Environment::class);
        $this->repository = m::mock(RepositoryInterface::class);

        $container = new Container();
        $container['twig'] = $this->twig;
        $container['person_repository'] = $this->repository;

        $this->controller = new IFrameController($container);
    }

    public function tearDown() : void
    {
        m::close();
        parent::tearDown();
    }

    public function testItCanReturnHtml()
    {
        $this->repository->shouldReceive('get')
            ->withArgs(['1'])
            ->once()
            ->andReturn($person = new Entity('1', 'John'));

        $this->twig->shouldReceive('render')
            ->once()
            ->withArgs(['i-frame.twig', ['person' => $person]])
            ->andReturn('<html></html>');

        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => 'id=1'
            ])
        );

        $response = new Response();

        /** @var Response $response */
        $response = $this->controller->iframe($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('<html></html>', (string)$response->getBody());
    }

    public function testMissingIdIsError()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => ''
            ])
        );

        $response = new Response();

        /** @var Response $response */
        $response = $this->controller->iframe($request, $response);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testPersonNotFound()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => 'id=1'
            ])
        );

        $this->repository->shouldReceive('get')
            ->withArgs(['1'])
            ->once()
            ->andReturn(null);

        $response = new Response();

        /** @var Response $response */
        $response = $this->controller->iframe($request, $response);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
