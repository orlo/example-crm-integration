<?php

namespace SocialSignIn\Test\ExampleCrmIntegration\Controller;

use Mockery as m;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use SocialSignIn\ExampleCrmIntegration\Controller\SearchController;
use SocialSignIn\ExampleCrmIntegration\Person\Entity;
use SocialSignIn\ExampleCrmIntegration\Person\RepositoryInterface;

/**
 * @covers \SocialSignIn\ExampleCrmIntegration\Controller\SearchController
 */
class SearchControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var SearchController
     */
    private $controller;

    /**
     * @var RepositoryInterface|m\Mock
     */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(RepositoryInterface::class);
        $container = new Container();
        $container['person_repository'] = $this->repository;
        $this->controller = new SearchController($container);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testItCanReturnHtml()
    {
        $this->repository->shouldReceive('search')
            ->withArgs(['john'])
            ->once()
            ->andReturn($persons = [new Entity('1', 'John'), new Entity('2', 'Johnny')]);

        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => 'q=john'
            ])
        );

        $response = new Response();

        /** @var Response $response */
        $response = $this->controller->search($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"results": [{"id":"1","name":"John"},{"id":"2","name":"Johnny"}]}',
            (string)$response->getBody()
        );
    }

    public function testMissingQueryIsError()
    {
        $request = Request::createFromEnvironment(
            Environment::mock([
                'QUERY_STRING' => ''
            ])
        );

        $response = new Response();

        /** @var Response $response */
        $this->setExpectedException(\InvalidArgumentException::class);
        $response = $this->controller->search($request, $response);
    }
}
