<?php

namespace SocialSignIn\ExampleCrmIntegration\Controller;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use SocialSignIn\ExampleCrmIntegration\Person\RepositoryInterface;

final class SearchController
{

    /**
     * @var RepositoryInterface
     */
    private $repository;

    public function __construct(Container $c)
    {
        $this->repository = $c['person_repository'];
    }

    public function search(Request $request, Response $response): Response
    {
        $query = $request->getQueryParam('q', null);

        if (empty($query)) {
            throw new \InvalidArgumentException("q empty or not specified");
        }

        return $response->withJson([
            'results' => $this->repository->search($query)
        ], 200);
    }
}
