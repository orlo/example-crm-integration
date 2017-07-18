<?php

use Assert\Assertion;
use Psr\Container\ContainerInterface;
use Slim\App;
use SocialSignIn\ExampleCrmIntegration\Controller\IFrameController;
use SocialSignIn\ExampleCrmIntegration\Controller\SearchController;
use SocialSignIn\ExampleCrmIntegration\Person\MockRepository;

Assertion::isInstanceOf($app, App::class);
Assertion::isInstanceOf($container, ContainerInterface::class);

$container['shared_secret'] = function () {
    return 'super-secret';
};

$container['person_repository'] = function () {
    return new MockRepository();
};

$container['twig'] = function () {
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
    return new Twig_Environment($loader, []);
};

$container['search_controller'] = function (ContainerInterface $c) {
    return new SearchController($c->get('person_repository'));
};

$container['i_frame_controller'] = function (ContainerInterface $c) {
    return new IFrameController($c->get('twig'), $c->get('person_repository'));
};
