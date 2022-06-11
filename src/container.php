<?php

use Assert\Assertion;
use Psr\Container\ContainerInterface;
use Slim\App;
use SocialSignIn\ExampleCrmIntegration\Controller\IFrameController;
use SocialSignIn\ExampleCrmIntegration\Controller\SearchController;
use SocialSignIn\ExampleCrmIntegration\Person\MockRepository;

if(!isset($app)) {
    throw new \LogicException('$app?');
}
if(!isset($container)) {
    throw new \LogicException('$container?');
}
Assertion::isInstanceOf($app, App::class);
Assertion::isInstanceOf($container, ContainerInterface::class);

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        error_log("Exception encountered " . get_class($exception) . ' / ' . $exception->getMessage());
        return $c['response']->withJson(['status' => 'error', 'error' => $exception->getMessage()], 500);
    };
};

$container['shared_secret'] = function () {
    $secret = getenv('SECRET');
    if (empty($secret)) {
        throw new \InvalidArgumentException("SECRET not defined in environment. Cannot continue.");
    }
    Assertion::notEmpty($secret);
    return $secret;
};

$container['person_repository'] = function () {
    return new MockRepository();
};

$container['twig'] = function () {
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
    return new Twig_Environment($loader, []);
};
