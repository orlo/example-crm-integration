<?php

use Assert\Assertion;
use Slim\App;

Assertion::isInstanceOf($app, App::class);

$app->get('/search', 'search_controller')->setName('search');
$app->get('/iframe', 'i_frame_controller')->setName('i-frame');
