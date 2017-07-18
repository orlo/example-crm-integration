<?php

namespace SocialSignIn\ExampleCrmIntegration\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use SocialSignIn\ExampleCrmIntegration\Person\RepositoryInterface;

final class SearchController
{

    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request, Response $response)
    {
        $query = $request->getQueryParam('q', null);

        if (empty($query)) {
            return $response->withStatus(400);
        }

        return $response->withJson([
            'data' => $this->repository->search($query)
        ], 200);
    }
}
