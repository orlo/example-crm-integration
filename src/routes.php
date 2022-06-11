<?php

use Assert\Assertion;
use Slim\App;

if(!isset($app)) {
    throw new \LogicException('$app?');
}

Assertion::isInstanceOf($app, App::class);

$app->get(
    '/search',
    \SocialSignIn\ExampleCrmIntegration\Controller\SearchController::class . ':search'
)->setName('search');

$app->get(
    '/iframe',
    \SocialSignIn\ExampleCrmIntegration\Controller\IFrameController::class . ':iframe'
)->setName('i-frame');
