<?php

namespace SocialSignIn\ExampleCrmIntegration\Controller;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use SocialSignIn\ExampleCrmIntegration\Person\RepositoryInterface;
use Twig_Environment;

final class IFrameController
{

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    public function __construct(Container $c)
    {
        $this->twig = $c['twig'];
        $this->repository = $c['person_repository'];
    }

    public function iframe(Request $request, Response $response): Response
    {
        $id = $request->getQueryParam('id', null);

        if (empty($id)) {
            return $response->withStatus(400);
        }

        $person = $this->repository->get($id);
        if ($person === null) {
            return $response->withStatus(404);
        }

        $response->write($this->twig->render('i-frame.twig', ['person' => $person]));
        return $response;
    }
}
